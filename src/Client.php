<?php

namespace Roublez\LogSnag;

use GuzzleHttp\Client as HttpClient;
use Psr\Http\Message\ResponseInterface;

class Client {

    /**
     * The LogSnag api token.
     *
     * @var string
     */
    public string $token;

    /**
     * The optional default project.
     *
     * @var string|null
     */
    public ?string $project;

    /**
     * The optional default channel.
     *
     * @var string|null
     */
    public ?string $channel;

    /**
     * The guzzle http client
     *
     * @var \GuzzleHttp\Client
     */
    private HttpClient $httpClient;

    /**
     * Constructs a new LogSnag client instance.
     *
     * @param string $token The LogSnag api token.
     * @param string|null $project The default project.
     * @param string|null $channel The default channel.
     */
    public function __construct (string $token, ?string $project = null, ?string $channel = null) {
        $this->token = $token;
        $this->project = $project;
        $this->channel = $channel;

        $this->httpClient = new HttpClient([
            'base_uri' => 'https://api.logsnag.com/v1/',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$this->token
            ],
            'http_errors' => false
        ]);
    }

    /**
     * Pushes an event to the specified project
     *
     * @param string|array $eventOrPayload The event name of the log or the full payload array
     * @param string|null $description The description of the event (ignored when an array is passed as the first parameter)
     * @param string|null $icon The optional icon of the event (ignored when an array is passed as the first parameter)
     * @param boolean|null $notify Whether to notify (ignored when an array is passed as the first parameter)
     * @param array|null $tags The list of tags (ignored when an array is passed as the first parameter)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function event (string|array $eventOrPayload, ?string $description = null, ?string $icon = null, ?bool $notify = null, ?array $tags = null) : ResponseInterface {
        $payload = is_array($eventOrPayload) ? $eventOrPayload : [
            'event' => $eventOrPayload,
            'description' => $description,
            'icon' => $icon,
            'notify' => $notify,
            'tags' => $tags
        ];

        $payload = array_merge([
            'project' => $this->project,
            'channel' => $this->channel
        ], array_filter($payload));

        return $this->httpClient->post('log', [ 'json' => $payload ]);
    }

    /**
     * Pushes an insight to the specified project
     *
     * @param string|array $titleOrPayload The string title of the insight or the full payload array
     * @param string|null $value The value of the insight (ignored when an array is passed as the first parameter)
     * @param string|null $icon The optional icon of the insight (ignored when an array is passed as the first parameter)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function insight (string|array $titleOrPayload, ?string $value = null, ?string $icon = null) : ResponseInterface {
        $payload = is_array($titleOrPayload) ? $titleOrPayload : [
            'title' => $titleOrPayload,
            'value' => $value,
            'icon' => $icon
        ];

        $payload = array_merge([
            'project' => $this->project
        ], array_filter($payload));

        return $this->httpClient->post('insight', [ 'json' => $payload ]);
    }
}
