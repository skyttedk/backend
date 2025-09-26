<?php
//https://github.com/PHPOffice/PHPExcel/wiki/User%20Documentation%20Overview%20and%20Quickstart%20Guide
//Bruges til at downlade en liste over genererede gavekort
//Sæt filter på den dag hvor de er generet, (skal fremadrettet lave et batchnr)

class GavekortRapport Extends reportBaseController{
    public function run() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=gavekort.csv');

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        $x = utf8_decode('Værdi');
        $y = utf8_decode('Udløbsdato');

        fwrite($output,"Gavekortnr.;Kodeord;'.$y.'\n");
        //fwrite($output,'Gavekortnr.;Kodeord;'.$x.';'.$y."\n");

        $giftcertificates = GiftCertificate::find('all',array(
//            'conditions' => array('no_series = ? AND certificate_no >= ?', 2,'1132000'),
       //     'conditions' => array('no_series = ? AND created_date = ?', 2,'2017-04-27'),
                   'conditions' => array('reservation_group = ?',7),
            'order' => 'no_series,id',
            'select' => 'id,no_series,certificate_no,value,expire_date,password'));

        foreach($giftcertificates as $giftcertificate)
        {
             fwrite($output,
             		$this->encloseWithQuotes($giftcertificate->certificate_no).";".
                    $this->encloseWithQuotes($giftcertificate->password).";".
                    $this->encloseWithQuotes($giftcertificate->expire_date->format('d-m-Y'))."\n"
                );
        }
 }

 function encloseWithQuotes($value)
{
    if (empty($value)) {
        return "";
    }
    $value = str_replace('"', '""', $value);
    return '="'.$value.'"';
}

}
?>