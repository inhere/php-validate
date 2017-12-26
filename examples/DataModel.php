<?php

/**
 * Class DataModel
 *
 * custom extend the trait 'ValidationTrait' like the class 'Validation'
 */
class DataModel
{
    use \Inhere\Validate\ValidationTrait;

    protected $data = [];

    protected $db;

    /**
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function create()
    {
        if ($this->validate()->fail()) {
            return false;
        }

        return $this->db->insert($this->getSafeData());
    }
}
