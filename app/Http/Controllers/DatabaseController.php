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
            // Verificar variáveis de ambiente
            $envVars = [
                'DB_CONNECTION' => env('DB_CONNECTION'),
                'DB_HOST' => env('DB_HOST'),
                'DB_PORT' => env('DB_PORT'),
                'DB_DATABASE' => env('DB_DATABASE'),
                'DB_USERNAME' => env('DB_USERNAME'),
                'DB_PASSWORD' => env('DB_PASSWORD') ? 'SET' : 'NOT SET'
            ];
            
            // Testar conexão MySQL
            $pdo = DB::connection()->getPdo();
            
            // Verificar tabelas MySQL
            $tables = DB::select("SHOW TABLES");
            
            // Testar query simples
            $result = DB::select('SELECT 1 as test_connection');
            
            return response()->json([
                'success' => true,
                'message' => 'Conexão MySQL funcionando!',
                'env_vars' => $envVars,
                'pdo_driver' => $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME),
                'database' => env('DB_DATABASE'),
                'host' => env('DB_HOST'),
                'tables_count' => count($tables),
                'test_query' => $result[0]->test_connection ?? 'failed'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro na conexão MySQL: ' . $e->getMessage(),
                'env_vars' => [
                    'DB_CONNECTION' => env('DB_CONNECTION'),
                    'DB_HOST' => env('DB_HOST'),
                    'DB_PORT' => env('DB_PORT'),
                    'DB_DATABASE' => env('DB_DATABASE'),
                    'DB_USERNAME' => env('DB_USERNAME'),
                    'DB_PASSWORD' => env('DB_PASSWORD') ? 'SET' : 'NOT SET'
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

    /**
     * Import data from MySQL dump
     */
    public function importData()
    {
        try {
            // Executar nossa migração específica de importação
            Artisan::call('migrate', [
                '--path' => 'database/migrations/2024_11_21_000001_import_mysql_data.php',
                '--force' => true
            ]);
            $output = Artisan::output();
            
            return response()->json([
                'success' => true,
                'message' => 'Dados importados com sucesso do dump MySQL',
                'output' => $output
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar dados: ' . $e->getMessage()
            ], 500);
        }
    }
}