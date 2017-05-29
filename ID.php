<?php
/**
 * ID
 * 
 * PHP class to generate identification numbers and strings. IDs are generated 
 * with a control character to enable verification of IDs programmatically.
 * 
 * Examples can be found here: https://github.com/jop-io/ID
 * 
 * @license https://opensource.org/licenses/MIT MIT
 * @version 0.1
 * @author Jonas Persson <jonas@rgv2.net>
 */
class ID {
    private $lenght;
    private $codeMap;
    private $charMap;
    private $pool;
    private $type;
    private $radix;
    
    /**
     * Constructor. 
     * 
     * @param string $type 
     * @param integer $lenght
     */
    public function __construct($type = "", $lenght = 0) {
        $this->SetPoolType($type);    
        $this->SetLength($lenght);
    }
    
    /**
     * Generate an ID.
     * 
     * @return string The ID
     */
    public function Generate() {
        $output = "";
        for ($i = 0; $i < $this->lenght-1; $i++) {
            $position = rand(0, $this->radix-1);
            $output  .= substr($this->pool, $position, 1);
        }
        return $output . $this->GenerateControlCharacter($output);
    }
    
    /**
     * Verify an ID.
     * 
     * The verification is done in three steps:
     * 
     *      1. Check the lenght of the ID
     *      2. Make sure the ID only have character from the active pool set
     *      3. Calculate and compare the ID's control character
     * 
     * @param string $input The ID to be verified
     * @return boolean Returns true if ID is valid, otherwise false
     */
    public function Validate($input = "") {
        if ($this->type === "safe") {
            $input = mb_strtoupper($input, "UTF-8");
        }
        if (mb_strlen($input, "UTF-8") !== $this->lenght) {
            return false;
        }
        $chars = preg_split('/(?<!^)(?!$)/u', $input);
        foreach ($chars as $char) {
            if (!isset($this->charMap[$char])) {
                return false;
            }
        }
        return $this->ValidateControlCharacter($input);
    }
    
    /**
     * Set the pool type to be used for generation of IDs. The following pool 
     * types are available:
     * 
     *      "alphanum" = 0-9, a-z, A-Z
     *      "aplha"    = a-z, A-Z
     *      "lower"    = a-z
     *      "upper"    = A-Z
     *      "numeric"  = 0-9
     *      "nozero"   = 1-9
     *    * "safe"     = 1-9, BCDFGHJKLMNPQRSTVWXZ
     * 
     *    * Safe means that vowels has been removed to prevent the risk of 
     *      inappropriate words  being generated within the ID. All letters are
     *      in uppercase to ease reading for humans. The digit "0" (zero) has 
     *      also been removed to prevent confusion with the letter "O".
     * 
     * @param string $type Pool type to be used, defaults to "alphanum" (aA-zZ, 0-9)
     */
    public function SetPoolType($type = "") {
        $pools = [
            "alphanum" => "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ",
            "alpha"    => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ",
            "lower"    => "abcdefghijklmnopqrstuvwxyz",
            "upper"    => "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
            "numeric"  => "0123456789",
            "nozero"   => "123456789",
            "safe"     => "123456789BCDFGHJKLMNPQRSTVWXZ"
        ];
        $this->type    = isset($pools[strtolower($type)]) ? strtolower($type) : "alphanum";
        $this->pool    = $pools[$this->type];
        $this->radix   = strlen($this->pool);
        $this->codeMap = str_split($this->pool);
        $this->charMap = array_flip(str_split($this->pool));
    }
    
    /**
     * Set length of ID. Length is used both for generation and verification of
     * IDs. The length must be at least 2 (two). Defaults to 32 (thirty-two).
     * 
     * @param integer $lenght Must be at least 2, defaults to 32
     */
    public function SetLength($lenght = 0) {
        $this->lenght = intval($lenght) > 1 ? intval($lenght) : 32;
    }
    
    /**
     * Generates a control character using Luhn mod N.
     * 
     * @param string $input The ID, without control character
     * @return string A control character
     */
    private function GenerateControlCharacter($input = "") {
        $chars  = str_split($input);
        $factor = 2;
        $sum    = 0;
        for ($i = count($chars)-1; $i >= 0; $i--) {
            $addend = $factor * $this->charMap[$chars[$i]];
            $factor = $factor === 2 ? 1 : 2;
            $sum   += intval($addend/$this->radix)+($addend%$this->radix);
        }
        $code = ($this->radix-($sum%$this->radix))%$this->radix;
        return $this->codeMap[$code];
    }
    
    /**
     * Validates an IDs control character using Luhn mod N.
     * 
     * @param string $input The complete ID, including the control character
     * @return boolean Returns true if the control character is valid, otherwise false
     */
    private function ValidateControlCharacter($input = "") {
        $chars  = str_split($input);
        $factor = 1;
        $sum    = 0;
        for ($i = count($chars)-1; $i >= 0; $i--) {
            $addend = $factor * $this->charMap[$chars[$i]];
            $factor = $factor === 2 ? 1 : 2;
            $sum   += intval($addend/$this->radix)+($addend%$this->radix);
        }
        return $sum % $this->radix === 0;
    }
}
