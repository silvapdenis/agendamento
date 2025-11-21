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
            // Testar conexão primeiro
            DB::connection()->getPdo();
            
            // Executar migrações
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            
            // Verificar se há tabelas criadas
            $tables = DB::select("SHOW TABLES");
            
            return response()->json([
                'success' => true,
                'message' => 'Migrações executadas com sucesso',
                'output' => $output,
                'tables_created' => count($tables),
                'connection_ok' => true
            ]);
        } catch (Exception $e) {
            // Capturar informações detalhadas do erro
            $errorInfo = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode()
            ];
            
            // Testar se é problema de conexão
            try {
                DB::connection()->getPdo();
                $connectionStatus = 'OK';
            } catch (Exception $connError) {
                $connectionStatus = 'FAILED: ' . $connError->getMessage();
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar migrações: ' . $e->getMessage(),
                'error_details' => $errorInfo,
                'connection_status' => $connectionStatus,
                'env_check' => [
                    'DB_HOST' => env('DB_HOST'),
                    'DB_DATABASE' => env('DB_DATABASE')
                ]
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
                'DB_PASSWORD' => env('DB_PASSWORD') ? 'SET' : 'NOT SET',
                'MYSQL_URL' => env('MYSQL_URL') ? 'SET' : 'NOT SET'
            ];
            
            // Tentar conexão direta com PDO primeiro
            $dsn = 'mysql:host=' . env('DB_HOST') . ';port=' . env('DB_PORT') . ';dbname=' . env('DB_DATABASE') . ';charset=utf8mb4';
            $pdo = new \PDO($dsn, env('DB_USERNAME'), env('DB_PASSWORD'), [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
            
            // Testar query simples com PDO direto
            $stmt = $pdo->query('SELECT 1 as test_connection, VERSION() as mysql_version');
            $result = $stmt->fetch();
            
            // Agora testar com Eloquent
            DB::connection()->getPdo();
            $eloquentTest = DB::select('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?', [env('DB_DATABASE')]);
            
            return response()->json([
                'success' => true,
                'message' => 'Conexão MySQL funcionando!',
                'env_vars' => $envVars,
                'pdo_direct' => [
                    'test_connection' => $result['test_connection'],
                    'mysql_version' => $result['mysql_version']
                ],
                'eloquent_test' => $eloquentTest[0]->count ?? 0,
                'database' => env('DB_DATABASE'),
                'host' => env('DB_HOST')
            ]);
        } catch (Exception $e) {
            // Tentar conexão via MYSQL_URL se a conexão normal falhar
            $mysqlUrl = env('MYSQL_URL');
            if ($mysqlUrl) {
                try {
                    $pdo = new \PDO($mysqlUrl, null, null, [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    ]);
                    $stmt = $pdo->query('SELECT 1 as test_connection');
                    $result = $stmt->fetch();
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Conexão MySQL via URL funcionando!',
                        'connection_method' => 'MYSQL_URL',
                        'test_result' => $result['test_connection']
                    ]);
                } catch (Exception $urlError) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro na conexão MySQL: ' . $e->getMessage(),
                        'mysql_url_error' => $urlError->getMessage(),
                        'env_vars' => [
                            'DB_CONNECTION' => env('DB_CONNECTION'),
                            'DB_HOST' => env('DB_HOST'),
                            'DB_PORT' => env('DB_PORT'),
                            'DB_DATABASE' => env('DB_DATABASE'),
                            'DB_USERNAME' => env('DB_USERNAME'),
                            'DB_PASSWORD' => env('DB_PASSWORD') ? 'SET' : 'NOT SET',
                            'MYSQL_URL' => env('MYSQL_URL') ? 'SET' : 'NOT SET'
                        ]
                    ], 500);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro na conexão MySQL: ' . $e->getMessage(),
                'env_vars' => [
                    'DB_CONNECTION' => env('DB_CONNECTION'),
                    'DB_HOST' => env('DB_HOST'),
                    'DB_PORT' => env('DB_PORT'),
                    'DB_DATABASE' => env('DB_DATABASE'),
                    'DB_USERNAME' => env('DB_USERNAME'),
                    'DB_PASSWORD' => env('DB_PASSWORD') ? 'SET' : 'NOT SET',
                    'MYSQL_URL' => env('MYSQL_URL') ? 'SET' : 'NOT SET'
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