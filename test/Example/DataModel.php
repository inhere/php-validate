<?php declare(strict_types=1);

namespace Inhere\ValidateTest\Example;

use Inhere\Validate\ValidationTrait;

/**
 * Class DataModel - custom extend the trait 'ValidationTrait' like the class 'Validation'
 */
class DataModel
{
    use ValidationTrait;

    protected $data = [];

    protected $db;

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function create()
    {
        if ($this->validate()->isFail()) {
            return false;
        }

        // return $this->db->insert($this->getSafeData());
        return 1;
    }
}
