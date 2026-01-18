<?php

namespace App\Services;

use GuzzleHttp\Client;

class AIChatService
{
    protected $client;
    protected $apiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = 'https://router.huggingface.co/v1/chat/completions';
    }

    public function chat(string $message): string
    {
        try {
            $response = $this->client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('HF_API_KEY'),
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => 'meta-llama/Meta-Llama-3-8B-Instruct',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' =>
                            'Bạn là trợ lý ẩm thực, chuyên tư vấn món ăn Việt Nam. Trả lời ngắn gọn, dễ hiểu.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $message
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 2000,
                    
                ],
                'timeout' => 60
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['choices'][0]['message']['content']
                ?? 'AI không phản hồi.';
        } catch (\Exception $e) {
            return 'Lỗi AI: ' . $e->getMessage();
        }
    }
}
