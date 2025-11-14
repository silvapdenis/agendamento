<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupWhatsAppConversations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:cleanup {--days=7 : Número de dias para manter conversas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpar conversas antigas do WhatsApp para liberar espaço';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = $this->option('days');
        
        $this->info("Limpando conversas mais antigas que {$days} dias...");
        
        $deleted = \App\Models\WhatsAppConversation::where('updated_at', '<', now()->subDays($days))->delete();
        
        $this->info("Foram removidas {$deleted} conversas antigas.");
        
        // Resetar conversas abandonadas há mais de 1 dia para estado inicial
        $reset = \App\Models\WhatsAppConversation::where('updated_at', '<', now()->subDay())
                                   ->where('state', '!=', 'initial')
                                   ->update([
                                       'state' => 'initial',
                                       'context' => json_encode([])
                                   ]);
        
        $this->info("Foram resetadas {$reset} conversas abandonadas.");
        
        return Command::SUCCESS;
    }
}
