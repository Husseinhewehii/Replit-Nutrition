<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI;

class TestOpenAIConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test OpenAI configuration and connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing OpenAI configuration...');
        
        // Check if API key is set
        $apiKey = config('openai.api_key');
        if (empty($apiKey) || $apiKey === 'your_openai_api_key_here') {
            $this->error('❌ OpenAI API key is not configured or is using placeholder value.');
            $this->line('Please set OPENAI_API_KEY in your .env file with a valid API key.');
            return 1;
        }
        
        $this->info('✅ API key is configured');
        
        // Test connection
        try {
            $this->info('Testing connection to OpenAI...');
            $models = OpenAI::models()->list();
            
            $this->info('✅ Successfully connected to OpenAI!');
            $this->line('Available models: ' . count($models->data));
            
            // Test the specific model we use
            $this->info('Testing gpt-4o-mini model...');
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'user', 'content' => 'Say "Hello from Laravel Nutrition app!"']
                ],
                'max_tokens' => 10,
            ]);
            
            $this->info('✅ Model test successful!');
            $this->line('Response: ' . $response->choices[0]->message->content);
            
            $this->info('🎉 OpenAI configuration is working perfectly!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ OpenAI connection failed: ' . $e->getMessage());
            
            if (str_contains($e->getMessage(), 'Incorrect API key')) {
                $this->line('Please check your OPENAI_API_KEY in .env file.');
            } elseif (str_contains($e->getMessage(), 'rate limit')) {
                $this->line('Rate limit exceeded. Please try again later.');
            } elseif (str_contains($e->getMessage(), 'model')) {
                $this->line('Model access issue. Check your OpenAI account permissions.');
            } elseif (str_contains($e->getMessage(), 'organization')) {
                $this->line('Add OPENAI_ORGANIZATION= to your .env file (empty value).');
            }
            
            return 1;
        }
    }
}
