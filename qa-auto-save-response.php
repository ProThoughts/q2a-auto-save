<?php

require_once QA_INCLUDE_DIR.'db/metas.php';

class qa_auto_save_response_page {
    
    function match_request($request) {
        $parts = explode ( '/', $request );
        
        return $parts [0] == 'autosave'; //&& $parts [1] == 'v1'; //&& sizeof ( $parts ) > 1;
    }
    
    function process_request($request) {
        header ( 'Content-Type: application/json' );
            
        $parts = explode ( '/', $request );
        $resource = $parts [1];
        if (sizeof($parts) > 2) {
            $resource = 'invalid';
        }
        
        /* 
         * Internal security (non for third-party applications)
         * 
         * */
        if (!qa_is_logged_in()) {
            http_response_code ( 401 );
            
            $ret_val = array ();
            
            $json_object = array ();
            
            $json_object ['statuscode'] = '401';
            $json_object ['message'] = 'Unauthorized';
            $json_object ['details'] = 'The user is not logged in.';
            
            array_push ( $ret_val, $json_object );
            echo json_encode ( $ret_val, JSON_PRETTY_PRINT );
            
            return;
        } 
        
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($resource) {
            case 'question' :
                if (strcmp($method, 'POST') == 0) {
                    $inputJSON = file_get_contents('php://input');
                    
                    echo $this->post_draft(AS_KEY_QUESTION, $inputJSON);
                } else {
                    echo $this->get_draft(AS_KEY_QUESTION);
                }
                break;
            
            case 'answer' :
                if (strcmp($method, 'POST') == 0) {
                    $inputJSON = file_get_contents('php://input');
                    
                    echo $this->post_draft(AS_KEY_ANSWER, $inputJSON);
                } else {
                    echo $this->get_draft(AS_KEY_ANSWER);
                }
                break;
            
            case 'comment' :
                if (strcmp($method, 'POST') == 0) {
                    $inputJSON = file_get_contents('php://input');
                    
                    echo $this->post_draft(AS_KEY_COMMENT, $inputJSON);
                } else {
                    echo $this->get_draft(AS_KEY_COMMENT);
                }
                break;
            
            default :
                http_response_code ( 400 );
                
                $ret_val = array ();
                
                $json_object = array ();
                
                $json_object ['statuscode'] = '400';
                $json_object ['message'] = 'Bad Request';
                $json_object ['details'] = 'The request URI does not match the API in the system, or the operation failed for unknown reasons.';
                
                array_push ( $ret_val, $json_object );
                echo json_encode ( $ret_val, JSON_PRETTY_PRINT );
        }
    }
    
    function get_draft($key)
    {
        $ret_val = array();
        
        $json_object = array();
        
        $userid = qa_get_logged_in_userid();
        $json = qa_db_usermeta_get($userid, $key);
        
        if (empty($json)) {            
            $json_object['title'] = '';
            $json_object['content'] = '';
            
        } else {
            $json_object = json_decode($json);
        }
        
        array_push($ret_val, $json_object);
        http_response_code(200);
        
        return json_encode($ret_val, JSON_PRETTY_PRINT);
    }
    
    function post_draft($key, $JSONdata)
    {
        $userid = qa_get_logged_in_userid();
        
        qa_db_usermeta_set($userid, $key, $JSONdata);
        
        $ret_val = array();
        
        $json_object = array();
        
        $json_object['statuscode'] = '200';
        $json_object['message'] = 'draft saved';
        $json_object['details'] = 'The content was saved.';
        
        array_push($ret_val, $json_object);
        
        http_response_code(200);
        
        return json_encode($ret_val, JSON_PRETTY_PRINT);
    }
}