<?php
class gameserver_other extends gameserver
{  
    public function getStatusInfo()
    {
        return $this->pingServer();
    }
}
?>
