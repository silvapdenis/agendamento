<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Exception;

class TestDatabase extends Command
{
    protected $signature = 'db:test';
    protected $description = 'Test database connection and show tables';

    public function handle()
    {
        $this->info('🔍 Testando conexão com banco de dados...');
        
        try {
            // Teste de conexão básico
            DB::connection()->getPdo();
            $this->info('✅ Conexão com banco estabelecida com sucesso!');
            
            // Verificar tabelas existentes
            $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
            $this->info('📋 Tabelas encontradas: ' . count($tables));
            
            if (count($tables) > 0) {
                foreach ($tables as $table) {
                    $this->line("- {$table->tablename}");
                }
            }
            
            // Verificar se existe tabela users
            try {
                $userCount = DB::table('users')->count();
                $this->info("👥 Usuários cadastrados: {$userCount}");
            } catch (Exception $e) {
                $this->warn("⚠️ Tabela users não encontrada ou vazia");
            }
            
            // Verificar migrações
            try {
                $migrations = DB::table('migrations')->count();
                $this->info("🔄 Migrações executadas: {$migrations}");
            } catch (Exception $e) {
                $this->warn("⚠️ Tabela migrations não encontrada");
            }
            
        } catch (Exception $e) {
            $this->error('❌ Erro na conexão: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}