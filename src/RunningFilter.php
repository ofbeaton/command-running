<?php

namespace Ofbeaton\Command;

/**
 * Class RunningFilter
 *
 * @since 2015-07-30
 */
class RunningFilter
{

    /**
     * @var array
     * @since 2015-07-30
     */
    protected $fields = [];

    /**
     * @var array
     * @since 2015-07-30
     */
    protected $mods = [];

    /**
     * @var array
     * @since 2015-07-30
     */
    protected $validFields = [
        'os',
        'pid',
        'user',
        'process',
    ];

    /**
     * @var array
     * @since 2015-07-30
     */
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

    /**
     * @var array
     * @since 2015-07-30
     */
    protected $defaultMods = [
        'os' => '=',
        'pid' => '=',
        'user' => '=',
        'process' => '~=',
    ];

    /**
     * @var array
     * @since 2015-07-30
     */
    protected $autoRegEx = [
        'os' => true,
        'pid' => true,
        'user' => true,
        'process' => false,
    ];


    /**
     * @param string $field Field.
     * @param string $value Regex.
     *
     * @return string
     * @throws \InvalidArgumentException Regex cannot contain space or tab.
     *
     * @since 2015-07-30
     */
    protected function transform($field, $value)
    {
        if (strpos($value, '/') === 0) {
          // we got a regex
            if (strpos($value, ' ') !== false || strpos($value, "\t") !== false) {
                $error = 'Field `'.$field.'` regular expression cannot contain a space or tab. Use \s+ instead';
                throw new \InvalidArgumentException($error);
            }
        } elseif ($this->autoRegEx[$field] === true) {
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


    /**
     * @param string $field Field.
     *
     * @return mixed Field value.
     * @throws \InvalidArgumentException Invalid field.
     *
     * @since 2015-07-30
     */
    public function getField($field)
    {
        if (in_array($field, $this->validFields) === false) {
            throw new \InvalidArgumentException('Field `'.$field.'` is not a valid field.');
        } elseif (isset($this->fields[$field]) === false) {
            return null;
        }

        return $this->fields[$field];
    }//end getField()


    /**
     * @param string $field Field.
     *
     * @return mixed Mod applied to a given Field.
     * @throws \InvalidArgumentException Invalid field.
     *
     * @since 2015-07-30
     */
    public function getMod($field)
    {
        if (in_array($field, $this->validFields) === false) {
            throw new \InvalidArgumentException('Field `'.$field.'` is not a valid field.');
        } elseif (isset($this->mods[$field]) === false) {
            return null;
        }

        return $this->mods[$field];
    }//end getMod()


    /**
     * @param string $field Field.
     * @param string $value New value.
     * @param string $mod   Mod for field.
     *
     * @return $this
     * @throws \InvalidArgumentException Invalid field.
     *
     * @since 2015-07-30
     */
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


    /**
     * @return mixed OS.
     *
     * @since 2015-07-30
     */
    public function getOs()
    {
        $result = $this->getField('os');
        return $result;
    }//end getOs()


    /**
     * @param string $os  Value.
     * @param string $mod Mod on OS field.
     *
     * @return $this
     *
     * @since 2015-07-30
     */
    public function setOs($os, $mod = null)
    {
        $this->setField('os', $os, $mod);
        return $this;
    }//end setOs()


    /**
     * @return int Value of PID field.
     *
     * @since 2015-07-30
     */
    public function getPid()
    {
        $result = $this->getField('pid');
        return $result;
    }//end getPid()


    /**
     * @param string $pid New Value.
     * @param string $mod Mod on PID field.
     *
     * @return $this
     *
     * @since 2015-07-30
     */
    public function setPid($pid, $mod = null)
    {
        $this->setField('pid', $pid, $mod);
        return $this;
    }//end setPid()


    /**
     * @return mixed Process.
     *
     * @since 2015-07-30
     */
    public function getProcess()
    {
        $result = $this->getField('process');
        return $result;
    }//end getProcess()


    /**
     * @param string $process Process.
     * @param string $mod     Mod.
     *
     * @return $this
     *
     * @since 2015-07-30
     */
    public function setProcess($process, $mod = null)
    {
        $this->setField('process', $process, $mod);
        return $this;
    }//end setProcess()


    /**
     * @return mixed User.
     *
     * @since 2015-07-30
     */
    public function getUser()
    {
        $result = $this->getField('user');
        return $result;
    }//end getUser()


    /**
     * @param string $user User.
     * @param string $mod  Mod.
     *
     * @return $this
     *
     * @since 2015-07-30
     */
    public function setUser($user, $mod = null)
    {
        $this->setField('user', $user, $mod);
        return $this;
    }//end setUser()


    /**
     * @param array $details List of fields.
     *
     * @return boolean are the details valid.
     * @throws \RuntimeException Missing field.
     *
     * @since 2015-07-30
     */
    public function isOk(array $details)
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


    /**
     * @param string $field       Field.
     * @param mixed  $userValue   Value to check.
     * @param mixed  $filterValue Filter to check value against.
     *
     * @return boolean
     *
     * @since 2015-07-30
     */
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
