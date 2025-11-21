<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Exception;

class DatabaseController extends Controller
{
    /**
     * Execute database migrations
     */
    public function migrate()
    {
        try {
            // Executar migrações
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            
            return response()->json([
                'success' => true,
                'message' => 'Migrações executadas com sucesso',
                'output' => $output
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar migrações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test database connection
     */
    public function test()
    {
        try {
            // Testar conexão
            DB::connection()->getPdo();
            
            // Verificar tabelas
            $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
            
            // Contar registros em algumas tabelas principais
            $counts = [];
            $mainTables = ['users', 'doctors', 'clinics', 'specialties', 'appointments'];
            
            foreach ($mainTables as $table) {
                try {
                    $counts[$table] = DB::table($table)->count();
                } catch (Exception $e) {
                    $counts[$table] = 'Tabela não existe';
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Conexão com banco estabelecida',
                'database' => config('database.connections.pgsql.database'),
                'host' => config('database.connections.pgsql.host'),
                'tables_count' => count($tables),
                'tables' => array_map(function($t) { return $t->tablename; }, $tables),
                'record_counts' => $counts
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro na conexão: ' . $e->getMessage(),
                'database_config' => [
                    'host' => config('database.connections.pgsql.host'),
                    'database' => config('database.connections.pgsql.database'),
                    'username' => config('database.connections.pgsql.username'),
                    'port' => config('database.connections.pgsql.port')
                ]
            ], 500);
        }
    }

    /**
     * Run seeders
     */
    public function seed()
    {
        try {
            Artisan::call('db:seed', ['--force' => true]);
            $output = Artisan::output();
            
            return response()->json([
                'success' => true,
                'message' => 'Seeders executados com sucesso',
                'output' => $output
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar seeders: ' . $e->getMessage()
            ], 500);
        }
    }
}