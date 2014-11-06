<?php

class CSVMailer
{
    /* ------------------------------------------------------------------ */
    /* ---------------------- Settings to customize --------------------- */
    /* ------------------------------------------------------------------ */
    
    const FILEPATH = '';
    const SUBJECT = 'Subject of message';
    const MESSAGE = 'Full text of message';
    const SENDER = 'your_email@goes_here.com';
    
    private $_cnt;
    
    /**
     * Init routine.
     */
    public function init()
    {
        $this->verifyInput();
        $this->executeMainLoop();
    }

    /**
     * Parses each line of input file and attempts to send e-mail to given receipient
     * with given files as attachement.
     * 
     * @param  array  $line Each line extracted from CSV file; see @link $this->executeMainLoop();
     */
    public function parseLine($line = array())
    {
        list($type, $file, $email) = $line;

        print 'Line '.$this->_cnt.': ';

        /**
         * Validate, if type (first argument) is correct.
         * 
         * First argument must be either 'T' or 'N' and it points to a specific 
         * folder, from which an attachement file will be taken (a subfolder of
         * folder, when you run mailer.php).
         */
        if(strtolower($type) !== 'n' && strtolower($type) !== 't') return $this->printError('incorrectType', $type);

        /**
         * Validate, if main file and signature file (second argument) exists.
         */
        $mainFile = $type.DIRECTORY_SEPARATOR.$file;
        $sigFile = $mainFile.'.XAdES';

        if(!file_exists($mainFile) || !file_exists($sigFile)) return $this->printError('missingFile', $mainFile);

        /**
         * Validate e-mail address.
         *
         * http://stackoverflow.com/a/12026863/1469208
         * http://php.net/manual/it/filter.examples.validation.php
         */
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) return $this->printError('invalidEmail', $email);

        print 'Sending e-mail to "'.$email.'". Result: ';

        $files = array($mainFile, $sigFile);
        $result = $this->sendEmailWithAttachements($email, $files);

        print ($result) ? 'OK' : 'ERROR';

        print "\n";
    }

    /**
     * Sends email with all attachements given as array.
     *
     * A modified version of: http://stackoverflow.com/a/4586659/1469208
     *
     * Returns standard PHP mail() function's result. Of course, it states only,
     * whether mail was accepted for the delivery. It in now way indicates, whether
     * e-mail was actually delivered to the receipient. Millions of bad things can
     * happen after this method returns TRUE. Plus, you have to correctly configure
     * your local MTA (mail transport agent) to actually successfully send e-mails.
     * 
     * @param  string $to   Receipient of the message.
     * @param  array $files An array of files to be attached to message.
     * 
     * @return boolean      Whether mail was accepted for delivery. See above note.
     */
    public function sendEmailWithAttachements($to, $files)
    {
        $uid = md5(uniqid(time()));

        $header = 
         "From: ".self::SENDER."\r\n"
        ."MIME-Version: 1.0\r\n"
        ."Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n"
        ."This is a multi-part message in MIME format.\r\n" 
        ."--".$uid."\r\n"
        ."Content-type:text/plain; charset=iso-8859-2\r\n"
        ."Content-Transfer-Encoding: 7bit\r\n\r\n"
        .self::MESSAGE."\r\n\r\n";

        foreach($files as $file)
        {
            $fileName = basename($file);
            $fileContent = chunk_split(base64_encode(file_get_contents($file)));

            $header .= 
             "--".$uid."\r\n"
            ."Content-Type: application/octet-stream; name=\"".$fileName."\"\r\n"
            ."Content-Transfer-Encoding: base64\r\n"
            ."Content-Disposition: attachment; filename=\"".$fileName."\"\r\n\r\n"
            .$fileContent."\r\n\r\n";
        }

        $header .= "--".$uid."--";

        return mail($to, self::SUBJECT, "", $header);
    }

    /**
     * Main loop.
     */
    public function executeMainLoop()
    {
        global $argv;

        if($fp = fopen($argv[1], 'r'))
        {
            $this->_cnt = 1;

            while($line = fgetcsv($fp, 0, ';'))
            {
                if(count($line) == 3)
                {
                    $this->parseLine($line);
                    
                    ++$this->_cnt;
                }
            }

            fclose($fp);
        }
        else $this->printError('missingInputFile', $argv[1], TRUE);
    }

    /**
     * Get script's input arguments.
     *
     * Note: "The script's filename is always passed as an argument to the script,
     * therefore the minimum value of $argc is 1". And that's why we have if($argc !== 2)
     * while we're expecting only _one_ argument.
     *
     * http://stackoverflow.com/a/11049118/1469208
     *
     * http://php.net/manual/en/reserved.variables.argc.php
     * http://php.net/manual/en/reserved.variables.argv.php
     * 
     * http://www.jarrodoberto.com/articles/2011/12/running-php-from-the-command-line-basics
     *
     * If you're unfamiliar with formatted / parametrized strings in PHP, read:
     *
     * http://php.net/manual/en/function.printf.php
     */
    public function verifyInput()
    {
        global $argc, $argv;

        if($argc !== 2) $this->printError('wrongInput', NULL, TRUE);

        if(!file_exists($argv[1])) $this->printError('missingInputFile', $argv[1], TRUE);
    }

    /**
     * Prints formatted (parametrized) error message.
     * 
     * Prints error message indentified by given key with variable inside replaced
     * with given variable value. If you're unfamiliar with formatted / parametrized
     * strings, you'll find more in PHP manual, at:
     *
     * http://php.net/manual/en/function.printf.php
     *
     * @param  string  $key      Key that indentifies error message to be displayed.
     * @param  mixed   $variable Value which should be put into error message.
     * @param  boolean $exit     Wether to exit entire script or just return FALSE.
     * 
     * @return boolean           Returns FALSE to break further processing of fault line.
     */
    public function printError($key, $variable = NULL, $exit = FALSE)
    {
        $errorMessages = array
        (
            'invalidEmail'=>'Invalid e-mail address (3rd argument). Found "%s".',
            'missingFile'=>'Missing main or signature file (2nd argument) for "%s".',
            'missingInputFile'=>'Unable to open "%s" file. File is missing or invalid.',
            'incorrectType'=>'Wrong type (1st argument). Found "%s" instead of either "N" or "T".',
            'wrongInput'=>"Incorrect number of arguments. Correct call example:\n  php mailer.php list.csv\n\nWhere:\n  list.csv -- CSV file with e-mails in form:\n  T|N;filename;receipient\n"
        );

        printf($errorMessages[$key], $variable);
        print "\n";

        if($exit) exit();

        return FALSE;
    }
}

$fd = new CSVMailer();
$fd->init();

?>