 
 <?php
/** 
 * 检证文件类型类 
 * 
 * @author Silver 
 */
class FileTypeValidation
{
    // 文件类型，不同的头信息 
    private static $_fileFormats = Array('jp2' => '0000000C6A502020', 
    '3gp' => '0000002066747970', '3gp5' => '0000001866747970', 
    'm4a' => '00000020667479704D3441', 'ico' => '00000100', 'spl' => '00000100', 
    'vob' => '000001BA', 'cur' => '00000200', 'wb2' => '00000200', 
    'wk1' => '0000020006040600', 'wk3' => '00001A0000100400', 
    'wk4' => '00001A0002100400', 'wk5' => '00001A0002100400', 
    '123' => '00001A00051004', 'qxd' => '00004D4D585052', 'mdf' => '010F0000', 
    'tr1' => '0110', 'rgb' => '01DA01010003', 'drw' => '07', 'dss' => '02647373', 
    'dat' => 'A90D000000000000', 'db3' => '03', 'qph' => '03000000', 
    'adx' => '80000020031204', 'db4' => '04', 'n' => 'FFFE0000', 
    'a' => 'FFFE0000', 'skf' => '07534B46', 'dtd' => '0764743264647464', 
    'db' => 'D0CF11E0A1B11AE1', 'pcx' => '0A050101', 'mp' => '0CED', 
    'doc' => 'D0CF11E0A1B11AE1', 'nri' => '0E4E65726F49534F', 
    'wks' => 'FF00020004040554', 'pf' => '1100000053434341', 
    'ntf' => '4E49544630', 'nsf' => '4E45534D1A01', 'arc' => '41724301', 
    'pak' => '5041434B', 'eth' => '1A350100', 'mkv' => '1A45DFA393428288', 
    'ws' => '1D7D', 'gz' => '1F8B08', 'tar.z' => '1FA0', 'ain' => '2112', 
    'lib' => '213C617263683E0A', 'msi' => 'D0CF11E0A1B11AE1', 'vmdk' => '4B444D', 
    'dsp' => '23204D6963726F73', 'amr' => '2321414D52', 'hdr' => '49536328', 
    'sav' => '24464C3240282329', 'eps' => 'C5D0D3C6', 'pdf' => '25504446', 
    'fdf' => '25504446', 'hqx' => '2854686973206669', 
    'log' => '2A2A2A2020496E73', 'ivr' => '2E524543', 'rm' => '2E524D46', 
    'rmvb' => '2E524D46', 'ra' => '2E7261FD00', 'au' => '646E732E', 
    'cat' => '30', 'evt' => '300000004C664C65', 'asf' => '3026B2758E66CF11', 
    'wma' => '3026B2758E66CF11', 'wmv' => '3026B2758E66CF11', 
    'wri' => 'BE000000AB', '7z' => '377ABCAF271C', 'psd' => '38425053', 
    'sle' => '414376', 'asx' => '3C', 'xdr' => '3C', 'dci' => '3C21646F63747970', 
    'manifest' => '3C3F786D6C2076657273696F6E3D', 
    'xml' => '3C3F786D6C2076657273696F6E3D22312E30223F3E', 
    'msc' => 'D0CF11E0A1B11AE1', 'fm' => '3C4D616B65724669', 
    'mif' => '56657273696F6E20', 'gid' => '4C4E0200', 'hlp' => '4C4E0200', 
    'dwg' => '41433130', 'syw' => '414D594F', 'abi' => '414F4C494E444558', 
    'aby' => '414F4C4442', 'bag' => '414F4C2046656564', 
    'idx' => '5000000020000000', 'ind' => '414F4C494458', 
    'pfc' => '414F4C564D313030', 'org' => '414F4C564D313030', 
    'vcf' => '424547494E3A5643', 'bin' => '424C4932323351', 'bmp' => '424D', 
    'dib' => '424D', 'prc' => '424F4F4B4D4F4249', 'bz2' => '425A68', 
    'tar.bz2' => '425A68', 'tbz2' => '425A68', 'tb2' => '425A68', 
    'rtd' => '43232B44A4434DA5', 'cbd' => '434246494C45', 'iso' => '4344303031', 
    'clb' => '434F4D2B', 'cpt' => '43505446494C45', 'cru' => '43525553482076', 
    'swf' => '465753', 'ctf' => '436174616C6F6720', 'dms' => '444D5321', 
    'adf' => '5245564E554D3A2C', 'dvr' => '445644', 'ifo' => '445644', 
    'cdr' => '52494646', 'vcd' => '454E545259564344', 'mdi' => '4550', 
    'e01' => '4C5646090D0AFF00', 'evtx' => '456C6646696C6500', 
    'qbb' => '458600000600', 'cpe' => '464158434F564552', 'flv' => '464C56', 
    'aiff' => '464F524D00', 'eml' => '582D', 'gif' => '47494638', 
    'pat' => '47504154', 'gx2' => '475832', 'sh3' => '4848474231', 
    'tif' => '4D4D002B', 'tiff' => '4D4D002B', 'mp3' => '494433', 
    'koz' => '49443303000000', 'crw' => '49491A0000004845', 'cab' => '4D534346', 
    'lit' => '49544F4C49544C53', 'chi' => '49545346', 'chm' => '49545346', 
    'jar' => '5F27A889', 'jg' => '4A47040E000000', 'kgb' => '4B47425F61726368', 
    'shd' => '68490000', 'lnk' => '4C00000001140200', 'obj' => '80', 
    'pdb' => 'ACED000573720012', 'mar' => '4D41723000', 'dmp' => '504147454455', 
    'hdmp' => '4D444D5093A7', 'mls' => '4D563243', 'mmf' => '4D4D4D440000', 
    'nvram' => '4D52564E', 'ppz' => '4D534346', 'snp' => '4D534346', 
    'tlb' => '4D53465402000100', 'dvf' => '4D535F564F494345', 
    'msv' => '4D535F564F494345', 'mid' => '4D546864', 'midi' => '4D546864', 
    'dsn' => '4D56', 'com' => 'EB', 'dll' => '4D5A', 'drv' => '4D5A', 
    'exe' => '4D5A', 'pif' => '4D5A', 'qts' => '4D5A', 'qtx' => '4D5A', 
    'sys' => 'FFFFFFFF', 'acm' => '4D5A', 'ax' => '4D5A900003000000', 
    'cpl' => 'DCDC', 'fon' => '4D5A', 'ocx' => '4D5A', 'olb' => '4D5A', 
    'scr' => '4D5A', 'vbx' => '4D5A', 'vxd' => '4D5A', '386' => '4D5A', 
    'api' => '4D5A900003000000', 'flt' => '76323030332E3130', 
    'zap' => '4D5A90000300000004000000FFFF', 
    'sln' => '4D6963726F736F66742056697375616C', 'jnt' => '4E422A00', 
    'jtp' => '4E422A00', 'cod' => '4E616D653A20', 'dbf' => '4F504C4461746162', 
    'oga' => '4F67675300020000', 'ogg' => '4F67675300020000', 
    'ogv' => '4F67675300020000', 'ogx' => '4F67675300020000', 'dw4' => '4F7B', 
    'pgm' => '50350A', 'pax' => '504158', 'pgd' => '504750644D41494E', 
    'img' => 'EB3C902A', 'zip' => '504B0304140000', 'docx' => '504B030414000600', 
    'pptx' => '504B030414000600', 'xlsx' => '504B030414000600', 
    'kwd' => '504B0304', 'odt' => '504B0304', 'odp' => '504B0304', 
    'ott' => '504B0304', 'sxc' => '504B0304', 'sxd' => '504B0304', 
    'sxi' => '504B0304', 'sxw' => '504B0304', 'wmz' => '504B0304', 
    'xpi' => '504B0304', 'xps' => '504B0304', 'xpt' => '5850434F4D0A5479', 
    'grp' => '504D4343', 'qemu' => '514649', 'abd' => '5157205665722E20', 
    'qsd' => '5157205665722E20', 'reg' => 'FFFE', 'sud' => '52454745444954', 
    'ani' => '52494646', 'cmx' => '52494646', 'ds4' => '52494646', 
    '4xm' => '52494646', 'avi' => '52494646', 'cda' => '52494646', 
    'qcp' => '52494646', 'rmi' => '52494646', 'wav' => '52494646', 
    'cap' => '58435000', 'rar' => '526172211A0700', 'ast' => '5343486C', 
    'shw' => '53484F57', 'cpi' => 'FF464F4E54', 'sit' => '5374756666497420', 
    'sdr' => '534D415254445257', 'cnv' => '53514C4F434F4E56', 
    'cal' => 'B5A2B0B3B3B0A5B5', 'info' => 'E310000100000000', 
    'uce' => '55434558', 'ufa' => '554641C6D2C1', 'pch' => '564350434830', 
    'ctl' => '56455253494F4E20', 'ws2' => '575332303030', 
    'lwp' => '576F726450726F', 'bdr' => '5854', 'zoo' => '5A4F4F20', 
    'ecf' => '5B47656E6572616C', 'vcw' => '5B4D535643', 
    'dun' => '5B50686F6E655D', 'sam' => '5B7665725D', 
    'cpx' => '5B57696E646F7773', 'cfg' => '5B666C7473696D2E', 
    'cas' => '5F434153455F', 'cbk' => '5F434153455F', 'arj' => '60EA', 
    'vhd' => '636F6E6563746978', 'csh' => '6375736800000002', 
    'p10' => '64000000', 'dex' => '6465780A30303900', 'dsw' => '64737766696C65', 
    'flac' => '664C614300000022', 'dbb' => '6C33336C', 'acd' => '72696666', 
    'ram' => '727473703A2F2F', 'dmg' => '78', 'lgc' => '7B0D0A6F20', 
    'lgd' => '7B0D0A6F20', 'pwi' => '7B5C707769', 'rtf' => '7B5C72746631', 
    'psp' => '7E424B00', 'wab' => '9CCBCB8D1375D211', 'wpf' => '81CDAB', 
    'png' => '89504E470D0A1A0A', 'aw' => '8A0109000000E108', 'hap' => '91334846', 
    'skr' => '9501', 'gpg' => '99', 'pkr' => '9901', 'qdf' => 'AC9EBD8F0000', 
    'pwl' => 'E3828596', 'dcx' => 'B168DE3A', 'tib' => 'B46E6844', 
    'acs' => 'C3ABCDAB', 'lbk' => 'C8007900', 'class' => 'CAFEBABE', 
    'dbx' => 'CFAD12FE', 'dot' => 'D0CF11E0A1B11AE1', 
    'pps' => 'D0CF11E0A1B11AE1', 'ppt' => 'D0CF11E0A1B11AE1', 
    'xla' => 'D0CF11E0A1B11AE1', 'xls' => 'D0CF11E0A1B11AE1', 
    'wiz' => 'D0CF11E0A1B11AE1', 'ac_' => 'D0CF11E0A1B11AE1', 
    'adp' => 'D0CF11E0A1B11AE1', 'apr' => 'D0CF11E0A1B11AE1', 
    'mtw' => 'D0CF11E0A1B11AE1', 'opt' => 'D0CF11E0A1B11AE1', 
    'pub' => 'D0CF11E0A1B11AE1', 'rvt' => 'D0CF11E0A1B11AE1', 
    'sou' => 'D0CF11E0A1B11AE1', 'spo' => 'D0CF11E0A1B11AE1', 
    'vsd' => 'D0CF11E0A1B11AE1', 'wps' => 'D0CF11E0A1B11AE1', 
    'ftr' => 'D20A0000', 'arl' => 'D42A', 'aut' => 'D42A', 'wmf' => 'D7CDC69A', 
    'efx' => 'DCFE', 'one' => 'E4525C7B8CD8A74D', 'rpm' => 'EDABEEDB', 
    'gho' => 'FEEF', 'ghs' => 'FEEF', 'wp' => 'FF575043', 'wpd' => 'FF575043', 
    'wpg' => 'FF575043', 'wpp' => 'FF575043', 'wp5' => 'FF575043', 
    'wp6' => 'FF575043', 'jfif' => 'FFD8FF', 'jpe' => 'FFD8FF', 
    'jpeg' => 'FFD8FF', 'jpg' => 'FFD8FF', 'mof' => 'FFFE23006C006900', 
    'ipa' => '504B03040A','map4'=>'0000001C');
    /** 
     * 检查文件类型 
     * 
     * @param string $filePath 文件路径 
     * @param string $fileExt 文件扩展名 
     * 
     * @return boolean 
     */
    public static function validation ($filePath, $fileExt)
    {
        // 文件格式未知 
        if (! isset(self::$_fileFormats[$fileExt])) {
            return false;
        }
        $length = strlen(self::$_fileFormats[$fileExt]);
        $bin = self::_readFile($filePath, $length);
        $fileHead = @unpack("H{$length}", $bin);
        // 判断文件头 
        if (strtolower(self::$_fileFormats[$fileExt]) == $fileHead[1]) {
            return true;
        }
        return false;
    }
    /** 
     * 读取文件内容 
     * 
     * @param string $filePath 文件路径 
     * @param integer $size 
     * 
     * @return string 
     */
    private function _readFile ($filePath, $size)
    {
        $file = fopen($filePath, "rb");
        $bin = fread($file, $size);
        fclose($file);
        return $bin;
    }
}
?> 
