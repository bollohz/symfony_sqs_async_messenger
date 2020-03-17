<?php
namespace App\Messenger\Messages;

class IngestionMessage {

    private $_body;
    private $_header;
    private $_messageAttributes;

    /**
     * @return mixed
     */
    public function getBody() {
        return $this->_body;
    }

    /**
     * @param mixed $body
     * @return IngestionMessage
     */
    public function setBody($body): IngestionMessage {
        $this->_body = $body;
        return $this;
    }

}
