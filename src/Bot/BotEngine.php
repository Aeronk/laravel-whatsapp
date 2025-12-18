// src/Bot/BotEngine.php
class BotEngine
{
    public function handle($message, $user)
    {
        if ($message === 'menu') {
            return [
                'type' => 'text',
                'text' => ['body' => 'Welcome! Reply 1 for Support, 2 for Sales']
            ];
        }

        return app(AIManager::class)->reply($message, $user);
    }
}
