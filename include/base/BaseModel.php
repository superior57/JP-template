<?php

class BaseModel implements ArrayAccess{
	
	
	public function __construct(array $values)
	{
		 $this->values = $values;
	}
	
    /**
     * 暗黙的に offsetExists が呼ばれたりはしない。
     * & を使ったリファレンス返しはできない。
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->values[$offset];
    }

    /**
     * & を使ったリファレンス渡しはできない。
     * $a[] = $value のように呼ばれた場合、
     * $offset には null が渡される。
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->values[$offset] = $value;
    }

    /**
     * isset で呼ばれる。
     * array_key_exists では呼ばれないので注意。
     * (この動きどうかと思う)
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->values[$offset]);
    }

    /**
     * unset で呼ばれる。
     * 暗黙的に offsetExists が呼ばれたりはしない。
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->values[$offset]);
    }
    
    public function refreshRegistTime(){
    	$this->values['regist'] = time();
    }
    
    public function refreshUpdateTime(){
    	$this->values['update_time'] = time();
    }
}