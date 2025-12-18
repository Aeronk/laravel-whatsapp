<?php

class WebhookController
{
    public function __invoke(Request $request)
    {
        if ($request->isMethod('get')) {
            abort_unless(
                $request->hub_verify_token === config('whatsapp.meta.verify_token'),
                403
            );

            return response($request->hub_challenge);
        }

        app(WebhookProcessor::class)->handle($request->all());
        return response()->json(['status' => 'ok']);
    }
}
