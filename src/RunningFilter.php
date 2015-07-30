<?php

namespace Ofbeaton\Command;

class RunningFilter
{

    protected $fields = [];

    protected $mods = [];

    protected $validFields = [
                              'os',
                              'pid',
                              'user',
                              'process',
    ];

    protected $validMods = [
    'os' => [
             '=',
             '!',
    ],
    'pid' => [
              '=',
              '!',
    ],
    'user' => [
               '=',
               '!',
    ],
    'process' => [
                  '=',
                  '!',
                  '~=',
                  '~!',
    ],
    ];

    protected $defaultMods = [
    'os' => '=',
    'pid' => '=',
    'user' => '=',
    'process' => '~=',
    ];

    protected $autoRegEx = [
    'os' => true,
    'pid' => true,
    'user' => true,
    'process' => false,
    ];


    protected function transform($field, $value)
    {
        if (strpos($value, '/') === 0) {
          // we got a regex
            if (strpos($value, ' ') !== false || strpos($value, "\t") !== false) {
                throw new \InvalidArgumentException('Field `'.$field.'` regular expression cannot contain a space or tab. Use \s+ instead');
            }
        } elseif ($autoRegEx[$field] === true) {
          // turn it into a regex
            if (in_array($this->mods[$field], ['=', '!']) === true) {
                $value = '^'.$value.'$';
            }

            $value = '/'.$value.'/';
        } else {
            throw new \InvalidArgumentException('Field `'.$field.' must be a valid format.');
        }

        return $value;
    }//end transform()


    public function getField($field)
    {
        if (in_array($field, $this->validFields) === false) {
            throw new \InvalidArgumentException('Field `'.$field.'` is not a valid field.');
        } elseif (isset($this->fields[$field]) === false) {
            return null;
        }

        return $this->fields[$field];
    }//end getField()


    public function getMod($field)
    {
        if (in_array($field, $this->validFields) === false) {
            throw new \InvalidArgumentException('Field `'.$field.'` is not a valid field.');
        } elseif (isset($this->mods[$field]) === false) {
            return null;
        }

        return $this->mods[$field];
    }//end getMod()


    public function setField($field, $value, $mod = null)
    {
        if (in_array($field, $this->validFields) === false) {
            throw new \InvalidArgumentException('Field `'.$field.'` is not a valid field.');
        } elseif ($mod !== null && in_array($mod, $this->validMods[$field]) === false) {
            throw new \InvalidArgumentException('Mod `'.$mod.'` is not valid for Field `'.$field.'`.');
        }

        $value = $this->transform($field, $value);

        $this->fields[$field] = $value;
        if ($mod === null) {
            $mod = $this->defaultMods[$field];
        }

        $this->mods[$field] = $mod;

        return $this;
    }//end setField()


    public function getOs()
    {
        $result = $this->getField('os');
        return $result;
    }//end getOs()


    public function setOs($os, $mod = null)
    {
        $this->setField('os', $os, $mod);
        return $this;
    }//end setOs()


    public function getPid()
    {
        $result = $this->getField('pid');
        return $result;
    }//end getPid()


    public function setPid($pid, $mod = null)
    {
        $this->setField('pid', $pid, $mod);
        return $this;
    }//end setPid()


    public function getProcess()
    {
        $result = $this->getField('process');
        return $result;
    }//end getProcess()


    public function setProcess($process, $mod = null)
    {
        $this->setField('process', $process, $mod);
        return $this;
    }//end setProcess()


    public function getUser()
    {
        $result = $this->getField('user');
        return $result;
    }//end getUser()


    public function setUser($user, $mod = null)
    {
        $this->setField('user', $user, $mod);
        return $this;
    }//end setUser()


    public function isOk($details)
    {
        foreach ($this->validFields as $field) {
            if (isset($details[$field]) === false) {
                throw new \RuntimeException('The filter did receive a user value for field `'.$field.'`.');
            } elseif ($this->match($field, $details[$field], $this->getField($field)) === false) {
                return false;
            }
        }

        return true;
    }//end isOk()


    protected function match($field, $userValue, $filterValue)
    {
        if ($filterValue === null) {
            return true;
        }

        $mod = $this->mods[$field];
        if (in_array($mod, ['=', '!', '~=', '~!']) === true) {
          // regex match
            $match = preg_match($filterValue, $userValue);

            if (in_array($mod, ['=', '~=']) === true && $match === 1) {
                return true;
            } elseif (in_array($mod, ['!', '~!']) === true && $match !== 1) {
                return true;
            }
        }

        return false;
    }//end match()
}//end class
