<?php

namespace GFUnit\lister\rapporter;

class ExportCSVSimple {
    private $data;
    private $filename;

    public function __construct(string $filename) {
      
        $this->filename = $filename;
    }
    
    public function exportSql($sql,$postProcessCallback=null) {
        $data = \Dbsqli::GetSql2($sql);

        foreach($data as $key=>$row) {
            if ($postProcessCallback) {
                $data[$key] = $postProcessCallback($row);
            }
        }

        $this->exportData($data);
    }

    public function exportData($data) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $this->filename . '.csv"');
        $fp = fopen('php://output', 'w');
        fputs($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        if (!empty($data)) {



            fputcsv($fp, array_keys(reset($data)),";");
            foreach ($data as $fields) {
                fputcsv($fp, $fields,";");
            }
        }
        fclose($fp);
    }
}
