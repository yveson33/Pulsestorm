#!/usr/bin/env php
<?php
    function input($string)
    {
        echo $string . "\n] ";
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);        
        fclose($handle);
        return $line;
    }
    
    function error($string)
    {
        echo "ERROR: " . $string . "\n";
        exit;
    }

    function get_config_path($code_pool, $package, $module)
    {
        $path = sprintf(
        'app/code/%s/%s/%s',$code_pool, $package, $module);
        
        $path = $path . '/etc/config.xml';
        if(is_file($path))
        {
            return $path;
        }
        error(sprintf("Could not find %s",$path));    
    }
    
    function get_version_index()
    {
        $answer = input("Increment\n" .
        "1. Major   (X.0.0)\n" .
        "2. Minor   (0.X.0)\n" .
        "3. Bug Fix (0.0.X)");
        $answer = trim($answer);
        if(!is_numeric($answer) || $answer > 3 || $answer < 1)
        {
            return get_version_index();
        }
        
        return $answer - 1;
    }
    function main($argv)
    {        
        if(!is_dir('app'))
        {
            error("Couldn't find \"app\" folder in path, please run from a Magento sub-directory.");
        }
        
        $code_pool  = trim(input("What code pool?"));
        $package    = trim(input("What package?"));
        $module     = trim(input("What module?"));

        $path = get_config_path($code_pool, $package, $module);
        
        echo "Loading: " . $path . "\n";
        $xml = simplexml_load_file($path);
        
        $xVersion = $xml->modules->{$package.'_'.$module}->version;
        $version  = (string) $xVersion;
        echo "Current Version: " . $version . "\n";

        //list($major, $minor, $bugfix) = explode(".", $version);
        
        $parts = explode(".", $version);
        
        $index = get_version_index();
        $parts[$index] ++;
        
        $version_new = implode(".",$parts);
        $xVersion[0] = $version_new;                
        $xml = $xml->asXml();
        if(simplexml_load_string($xml))
        {
            file_put_contents($path, $xml);
        }
        echo "Updated $path to $version_new\n";
    }
    main($argv);