<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send SMS to a specific number.
     *
     * @param string $to
     * @param string $message
     * @return bool
     */
    public function send($to, $message)
    {
        try {
            // Log the attempt
            Log::info("SMS Service: Sending message to {$to}", ['message' => $message]);

            // TODO: Integrate with actual SMS Gateway Provider
            // Example implementation:
            // $response = Http::post('https://api.sms-provider.com/send', [
            //     'api_key' => config('services.sms.key'),
            //     'to' => $to,
            //     'message' => $message
            // ]);

            // For now, return true to simulate success
            return true;
        } catch (\Exception $e) {
            Log::error("SMS Service Error: " . $e->getMessage());
            return false;
        }
    }
}
