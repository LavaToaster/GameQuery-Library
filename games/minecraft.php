<?php
class gameserver_minecraft extends gameserver
{
    public $game = "minecraft";

    const PREFIX = "\xFE\xFD";
    const CHALLENGE = "\x09";
    const QUERY = "\x00";    

    protected $socket;
    private $info = array();
    public $getValues = array(
        'hostname',
        'version',
        'plugins',
        'map',
        'numplayers',
        'maxplayers'
    );
    
    protected $packet = array();

    protected function doExecute($serverInfo){
        $this->serverInfo = $serverInfo;
        $this->packet['id_packed'] = "\x00\x00\x00\x00";
        $this->packet['challenge_packed'] = "\x00\x00\x00\x00";
    }

    public function __destruct(){
        if($this->socket)
            $this->disconnect();       
    }

    public function connect(){
        if($this->socket = fsockopen("udp://".$this->serverInfo['ip'], $this->serverInfo['port'], $errno, $errstr, 2))
        {
            $this->serverOnline = true;
            stream_set_timeout($this->socket,2);
        }else{
            $this->error[] = "Server offline / Cannot connect to server";
        }
    }

    public function disconnect(){
        fclose($this->socket);
    }

    public function getStatusInfo()
    {
        if(!$this->handshake())
            return false;

        $query = $this->sendPacket(self::QUERY, $this->packet['id_packed']);
        //$query = substr($query, 5);// Might need to double check on this one It could just be the code
        
        $query = substr($query, 16);
        $info = explode("\x00\x00\x01player_\x00\x00", $query);
        $info[1] = SubStr( $info[1], 0, -2 );
        $players = explode("\x00", $info[1]);
        $serverInfo = explode("\x00", $info[0]);

        $i=0;
        $server = array();
        foreach($serverInfo as $data)
        {
            if(in_array($data, $this->getValues)){
                $server[$data] = $serverInfo[$i+1];
            }
            $i++;
        }
        
        print_r($server);
    }     

    /**
    * Handshakes with the server
    * 
    * @link credit to http://pastebin.com/Qypvrt3z for code to interpret the new challenge id
    */
    public function handshake(){
        $reply = $this->sendPacket(self::CHALLENGE); // IT's TIME TO D-D-D-DDDUEL

        //Is it a valid?
        if($reply[0] != "\x09")
            return false;

        //Huzzah We can continue        

        $token = 0;
        for($i = 5; $i < (strlen($reply) - 1); $i++)
        {
            $token *= 10;
            $token += $reply[$i];
        }

        // Divide the int32 into 4 bytes
        $token_arr = array(     0 => ($token / (256*256*256)) % 256,
            1 => ($token / (256*256)) % 256,
            2 => ($token / 256) % 256,
            3 => ($token % 256)
        );

        $token_arr = array_map('chr', $token_arr);

        $this->packet['challenge_packed'] =  $token_arr[0] . $token_arr[1] . $token_arr[2] . $token_arr[3];//$hex_arr[9].$hex_arr[10].$hex_arr[11].$hex_arr[12];

        return true;
    }

    public function sendPacket($type, $data = ''){
        $packet = $type . $this->packet['id_packed'] . $this->packet['challenge_packed'] . $data;
        return $this->sendRawPacket($packet);
    }

    public function sendRawPacket($data, $prefix = true, $len = 2048){
        $data = self::PREFIX.$data;

        $send = fwrite($this->socket, $data, strlen($data));
        $read = fread($this->socket, $len);
        return $read;
    }

    public function Immajustmoveyouheresmileyface(){
        $data = substr($data, 9);
        $data = explode("\x00\x00", $data);

        $this->serverStatus['protocol'] = $data[0];
        $this->serverStatus['serverVersion'] = $data[1];
        $this->serverStatus['motd'] = $data[2];
        $this->serverStatus['online_players'] = $data[3];
        $this->serverStatus['max_players'] = $data[4];

        unset($data);
    }
}