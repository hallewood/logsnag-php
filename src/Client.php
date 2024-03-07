<?php

namespace Hallewood\LogSnag;

final class Client
{
    /**
     * Constructs a new LogSnag client instance.
     *
     * @param string $token The LogSnag api token.
     * @param string $project The project to use.
     */
    public function __construct(
        private readonly string $token,
        private readonly string $project,
    ) {}

    /**
     * Sends a request to the LogSnag API.
     *
     * @param string $method The HTTP method.
     * @param string $uri The HTTP uri.
     * @param array $data The data to send.
     */
    private function request(string $method, string $uri, array $data = []): array
    {
        $payload = \json_encode($data + ['project' => $this->project]);

        $request = curl_init($uri);
        curl_setopt($request, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: '.strlen($payload),
            'Authorization: Bearer '.$this->token,
        ]);

        curl_setopt($request, \CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($request, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($request);

        if ($response === false) {
            throw new \Exception('Request failed: '.curl_error($request));
        }

        curl_close($request);

        return json_decode($response, true);
    }

    /**
     * Validates the key-value contents of a property.
     *
     * @param string $property The property name.
     * @param array<mixed> $contents The property contents.
     * @return array<string,string> The validated contents.
     *
     * @throws \Hallewood\LogSnag\InvalidMessageException
     */
    private function validateKeyValueType(string $property, array $contents): array
    {
        foreach ($contents as $key => $value) {
            if (! is_string($key) || ! \preg_match('/^[a-z-]+$/', $key)) {
                throw new InvalidMessageException('The key ['.$key.'] of the "'.$property.'" property is invalid. Keys must be strings and may only contain lowercase letters and hyphens.');
            }

            $stringable = $value === null || is_scalar($value) || (is_object($value) && method_exists($value, '__toString'));
            if (! $stringable) {
                throw new InvalidMessageException('The value for the key ['.$key.'] of the "'.$property.'" property is invalid. Values must always be stringable.');
            }

            $contents[$key] = (string) $value;
        }

        return $contents;
    }

    /**
     * Logs an event.
     *
     * @param string $channel The channel name.
     * @param string $event The name of the event (basically the message).
     * @param null|string $userId The id of the user to link the entry to.
     * @param null|string $description The description of the event.
     * @param null|string $icon An optional icon to display with the event.
     * @param null|bool $notify Whether to send a push notification.
     * @param null|array<string,string> $tags A list of tags to append to the entry.
     * @param null|string $parser Which parser to use (possible values are "markdown" or "text").
     * @param null|int $timestamp An optional timestamp for historical data.
     *
     * @throws \Hallewood\LogSnag\InvalidMessageException
     */
    public function log(
        string $channel,
        string $event,
        ?string $userId = null,
        ?string $description = null,
        ?string $icon = null,
        ?bool $notify = null,
        ?array $tags = null,
        ?string $parser = null,
        ?int $timestamp = null
    ) : array
    {
        if (isset($tags)) {
            $tags = $this->validateKeyValueType('tags', $tags);
        }

        if (isset($parser) && ! in_array($parser, ['markdown', 'text'])) {
            throw new InvalidMessageException('The parser ['.$parser.'] is not supported. Supported parsers are "markdown" and "text".');
        }

        if (isset($timestamp) && (! \is_numeric($timestamp) || $timestamp < 0)) {
            throw new InvalidMessageException('The timestamp ['.$timestamp.'] is not a valid UNIX timestamp.');
        }

        $payload = [
            'channel' => $channel,
            'event' => $event,
        ];

        isset($userId) && $payload['user_id'] = $userId;
        isset($description) && $payload['description'] = $description;
        isset($icon) && $payload['icon'] = $icon;
        isset($notify) && $payload['notify'] = $notify;
        isset($tags) && $payload['tags'] = $tags;
        isset($parser) && $payload['parser'] = $parser;
        isset($timestamp) && $payload['timestamp'] = $timestamp;

        return $this->request('POST', 'https://api.logsnag.com/v1/log', $payload);
    }

    /**
     * Identifies a user.
     *
     * @param string $userId The id of the user to identify.
     * @param array<string,string> $properties The properties of the user.
     *
     * @throws \Hallewood\LogSnag\InvalidMessageException
     */
    public function identify(string $userId, array $properties)
    {
        $properties = $this->validateKeyValueType('properties', $properties);

        $this->request('POST', 'https://api.logsnag.com/v1/identify', [
            'user_id' => $userId,
            'properties' => $properties,
        ]);
    }

    /**
     * Publishes an insight.
     *
     * @param string $title The title of the insight.
     * @param string|int|float $value The value of the insight.
     * @param null|string $icon An optional icon to display with the event.
     */
    public function insight(string $title, string|int|float $value, ?string $icon = null): array
    {
        $payload = [
            'title' => $title,
            'value' => $value,
        ];

        isset($icon) && $payload['icon'] = $icon;

        return $this->request('POST', 'https://api.logsnag.com/v1/insight', $payload);
    }

    /**
     * Mutates an insight.
     *
     * At least one of the available mutations must be provided.
     *
     * @param string $title The title of the insight.
     * @param int|null $inc Applies the increment mutation. This mutation increases or decreases the value of the insight.
     * @param null|string $icon An optional icon to display with the event.
     *
     * @throws \Hallewood\LogSnag\InvalidMessageException
     */
    public function insightMutate(string $title, ?int $inc = null, ?string $icon = null): array
    {
        $mutations = [];

        isset($inc) && $mutations['$inc'] = $inc;

        if (count($mutations) === 0) {
            throw new InvalidMessageException('At least one mutation must be provided.');
        }

        $payload = [
            'title' => $title,
            'value' => $mutations,
        ];

        isset($icon) && $payload['icon'] = $icon;

        return $this->request('PATCH', 'https://api.logsnag.com/v1/insight', $payload);
    }
}
