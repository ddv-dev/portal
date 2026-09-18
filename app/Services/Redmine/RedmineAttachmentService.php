<?php

namespace App\Services\Redmine;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RedmineAttachmentService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.redmine.url'), '/');
        $this->apiKey  = config('services.redmine.api_key');
    }

    /**
     * Загрузить файл в Redmine, вернуть token.
     */
    public function upload(UploadedFile $file): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-Redmine-API-Key' => $this->apiKey,
                'Content-Type'      => 'application/octet-stream',
            ])->withBody(
                file_get_contents($file->getRealPath()),
                'application/octet-stream'
            )->post("{$this->baseUrl}/uploads.json");

            if (!$response->successful()) {
                Log::error('Redmine upload failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            $token = $data['upload']['token'] ?? null;

            if (!$token) {
                Log::error('Redmine upload: no token', ['data' => $data]);
                return null;
            }

            return [
                'token'        => $token,
                'filename'     => $file->getClientOriginalName(),
                'content_type' => $file->getMimeType() ?: 'application/octet-stream',
            ];
        } catch (\Throwable $e) {
            Log::error('Redmine upload exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Загрузить несколько файлов.
     */
    public function uploadMany(array $files): array
    {
        $uploads = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) continue;
            $u = $this->upload($file);
            if ($u) $uploads[] = $u;
        }
        return $uploads;
    }

    /**
     * Прикрепить uploads к задаче через API.
     */
    public function attachToIssue(int $issueId, array $uploads, ?string $notes = null): bool
    {
        $payload = ['issue' => ['uploads' => $uploads]];
        if ($notes !== null) {
            $payload['issue']['notes'] = $notes;
        }

        $response = Http::withHeaders([
            'X-Redmine-API-Key' => $this->apiKey,
            'Content-Type'      => 'application/json',
        ])->put("{$this->baseUrl}/issues/{$issueId}.json", $payload);

        if (!$response->successful()) {
            Log::error('Redmine attachToIssue failed', [
                'issue_id' => $issueId,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);
            return false;
        }

        return true;
    }
}