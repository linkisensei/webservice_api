<?php namespace webservice_api\http\middlewares\rate_limiting;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\RequestHandlerInterface;
use \moodle_database;
use Laminas\Diactoros\Response\JsonResponse;

class authenticated_rate_limit implements MiddlewareInterface {

    private moodle_database $db;
    private int $time_window;
    private int $max_requests;
    private string $identifier;
    private string $table_name = 'webservice_api_ratelimit';

    public function __construct(int $max_requests = 100, int $time_window = 60, string $identifier = '*'){
        global $DB;

        $this->max_requests = $max_requests;
        $this->time_window = $time_window;
        $this->identifier = $identifier;
        $this->db = $DB;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        if(!$this->can_proceed_with_request()){
            return $handler->handle($request);
        }

        return new JsonResponse(['message' => 'too many requests'], 429);
    }

    private function can_proceed_with_request() : bool {
        global $USER;

        if(empty($USER)){
            return true; // Unauthenticated
        }

        switch ($this->db->get_dbfamily()) {
            case 'mysql':
            case 'mariadb':
                return $this->can_proceed_mysql($USER->id);
            case 'sqlsrv':
                return $this->can_proceed_sqlserver($USER->id);
            case 'pgsql':
                return $this->can_proceed_postgres($USER->id);
            case 'oci':
                return $this->can_proceed_oracle($USER->id);
            default:
                return $this->can_proceed_default($USER->id);
        }
    }

    private function can_proceed_mysql(int $userid) : bool {
        $sql = "INSERT INTO {{$this->table_name}} (userid, identifier, requests, lastreset)
                VALUES (:userid, :identifier, 1, :current_time)
                ON DUPLICATE KEY UPDATE
                requests = IF(lastreset + :time_window <= :current_time, 1, requests + 1),
                lastreset = IF(lastreset + :time_window <= :current_time, :current_time, lastreset)";

        $params = [
            'userid' => $userid,
            'identifier' => $this->identifier,
            'current_time' => time(),
            'time_window' => $this->time_window
        ];

        $this->db->execute($sql, $params);

        $record = $this->db->get_record($this->table_name, [
            'userid' => $userid,
            'identifier' => $this->identifier
        ]);

        return $record->requests <= $this->max_requests;
    }

    private function execute_query_and_check(string $sql, int $userid) : bool {
        $params = [
            'userid' => $userid,
            'identifier' => $this->identifier,
            'current_time' => time(),
            'time_window' => $this->time_window
        ];
    
        $this->db->execute($sql, $params);
    
        $record = $this->db->get_record($this->table_name, [
            'userid' => $userid,
            'identifier' => $this->identifier
        ]);
    
        return $record->requests <= $this->max_requests;
    }

    private function can_proceed_sqlserver(int $userid) : bool {
        $sql = "MERGE INTO {{$this->table_name}} AS target
                USING (SELECT :userid AS userid, :identifier AS identifier) AS source
                ON target.userid = source.userid AND target.identifier = source.identifier
                WHEN MATCHED THEN
                    UPDATE SET
                        requests = CASE WHEN target.lastreset + :time_window <= :current_time THEN 1 ELSE target.requests + 1 END,
                        lastreset = CASE WHEN target.lastreset + :time_window <= :current_time THEN :current_time ELSE target.lastreset END
                WHEN NOT MATCHED THEN
                    INSERT (userid, identifier, requests, lastreset)
                    VALUES (:userid, :identifier, 1, :current_time);";
    
        return $this->execute_query_and_check($sql, $userid);
    }
    
    private function can_proceed_postgres(int $userid) : bool {
        $sql = "INSERT INTO {{$this->table_name}} (userid, identifier, requests, lastreset)
                VALUES (:userid, :identifier, 1, :current_time)
                ON CONFLICT (userid, identifier) DO UPDATE
                SET
                    requests = CASE WHEN {{$this->table_name}}.lastreset + :time_window <= :current_time THEN 1 ELSE {{$this->table_name}}.requests + 1 END,
                    lastreset = CASE WHEN {{$this->table_name}}.lastreset + :time_window <= :current_time THEN :current_time ELSE {{$this->table_name}}.lastreset END;";
    
        return $this->execute_query_and_check($sql, $userid);
    }
    
    private function can_proceed_oracle(int $userid) : bool {
        $sql = "MERGE INTO {{$this->table_name}} target
                USING (SELECT :userid AS userid, :identifier AS identifier FROM dual) source
                ON (target.userid = source.userid AND target.identifier = source.identifier)
                WHEN MATCHED THEN
                    UPDATE SET
                        requests = CASE WHEN target.lastreset + :time_window <= :current_time THEN 1 ELSE target.requests + 1 END,
                        lastreset = CASE WHEN target.lastreset + :time_window <= :current_time THEN :current_time ELSE target.lastreset END
                WHEN NOT MATCHED THEN
                    INSERT (userid, identifier, requests, lastreset)
                    VALUES (:userid, :identifier, 1, :current_time);";
                    
        return $this->execute_query_and_check($sql, $userid);
    }

    private function can_proceed_default(int $userid) : bool {
        $current_time = time();
    
        $record = $this->db->get_record($this->table_name, [
            'userid' => $userid,
            'identifier' => $this->identifier
        ]);
    
        if ($record) {
            if ($record->lastreset + $this->time_window <= $current_time) {
                $record->requests = 1;
                $record->lastreset = $current_time;
            } else {
                $record->requests += 1;
            }
    
            $this->db->update_record($this->table_name, $record);
        } else {
            $record = new \stdClass();
            $record->userid = $userid;
            $record->identifier = $this->identifier;
            $record->requests = 1;
            $record->lastreset = $current_time;
    
            $this->db->insert_record($this->table_name, $record);
        }
    
        return $record->requests <= $this->max_requests;
    }
    
}