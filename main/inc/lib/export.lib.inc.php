<?php
/* See license terms in /license.txt */

use Ddeboer\DataImport\Writer\ExcelWriter;
use Ddeboer\DataImport\Writer\CsvWriter;
use Ddeboer\DataImport\Workflow;
use Ddeboer\DataImport\Reader\CsvReader;
use Ddeboer\DataImport\Reader\ArrayReader;
use Ddeboer\DataImport\Writer\ArrayWriter;

/**
 *  This is the export library for Chamilo.
 *	Include/require it in your code to use its functionality.
 *	Several functions below are adaptations from functions distributed by www.nexen.net
 *
 *  @package chamilo.library
 */
class Export
{
	private function __construct()
    {
	}

	/**
	 * Export tabular data to CSV-file
	 * @param array $data
	 * @param string $filename
	 */
	public static function arrayToCsv($data, $filename = 'export')
    {
        if (empty($data)) {
            return false;
        }

        $reader = new ArrayReader($data);

        // Create the workflow from the reader
        $workflow = new Workflow($reader);

        $filePath = api_get_path(SYS_ARCHIVE_PATH).uniqid('').'.csv';

        $file = new \SplFileObject($filePath, 'w');
        $writer = new CsvWriter($file);
        $workflow->addWriter($writer);
        $workflow->process();
exit;
        DocumentManager::file_send_for_download($filePath, false, $filename.'.csv');
        exit;

	}

    /**
     * Export tabular data to XLS-file
     * @param array $data
     * @param string $filename
     */
    public static function arrayToXls($data, $filename = 'export', $encoding = 'utf-8')
    {
        $filePath = api_get_path(SYS_ARCHIVE_PATH).uniqid('').'.xls';

        $file = new \SplFileObject($filePath, 'w');
        $writer = new ExcelWriter($file);
        $writer->prepare();

        foreach ($data as $index => $row) {
            $writer->writeItem($row);
        }

        $writer->finish();

        DocumentManager::file_send_for_download($filePath, false, $filename.'.xls');
        exit;
	}

    /**
     * Export tabular data to XLS-file (as html table)
     * @param array $data
     * @param string $filename
     */
    public static function export_table_xls_html($data, $filename = 'export', $encoding = 'utf-8')
    {
        $file = api_get_path(SYS_ARCHIVE_PATH).uniqid('').'.xls';
        $handle = fopen($file, 'a+');
        $systemEncoding = api_get_system_encoding();
        fwrite($handle, '<!DOCTYPE html><html><meta http-equiv="Content-Type" content="text/html" charset="utf-8" /><body><table>');
        foreach ($data as $id => $row) {
            foreach ($row as $id2 => $row2) {
                $data[$id][$id2] = api_htmlentities($row2);
            }
        }
        foreach ($data as $row) {
            $string = implode("</td><td>", $row);
            $string = '<tr><td>' . $string . '</td></tr>';
            if ($encoding != 'utf-8') {
                $string = api_convert_encoding($string, $encoding, $systemEncoding);
            }
            fwrite($handle, $string."\n");
        }
        fwrite($handle, '</table></body></html>');
        fclose($handle);
        DocumentManager::file_send_for_download($file, false, $filename.'.xls');
    }

    /**
    * Export tabular data to XML-file
    * @param array  Simple array of data to put in XML
    * @param string Name of file to be given to the user
    * @param string Name of common tag to place each line in
    * @param string Name of the root element. A root element should always be given.
    * @param string Encoding in which the data is provided
    */
	public static function arrayToXml($data, $filename = 'export', $item_tagname = 'item', $wrapper_tagname = null, $encoding = null)
    {
		if (empty($encoding)) {
			$encoding = api_get_system_encoding();
		}
		$file = api_get_path(SYS_ARCHIVE_PATH).'/'.uniqid('').'.xml';
		$handle = fopen($file, 'a+');
		fwrite($handle, '<?xml version="1.0" encoding="'.$encoding.'"?>'."\n");
		if (!is_null($wrapper_tagname)) {
			fwrite($handle, "\t".'<'.$wrapper_tagname.'>'."\n");
		}
		foreach ($data as $row) {
			fwrite($handle, '<'.$item_tagname.'>'."\n");
			foreach ($row as $key => $value) {
				fwrite($handle, "\t\t".'<'.$key.'>'.$value.'</'.$key.'>'."\n");
			}
			fwrite($handle, "\t".'</'.$item_tagname.'>'."\n");
		}
		if (!is_null($wrapper_tagname)) {
			fwrite($handle, '</'.$wrapper_tagname.'>'."\n");
		}
		fclose($handle);
		DocumentManager :: file_send_for_download($file, true, $filename.'.xml');
		return false;
	}

    /**
     * Export hierarchical tabular data to XML-file
     * @param array  Hierarchical array of data to put in XML, each element presenting a 'name' and a 'value' property
     * @param string Name of file to be given to the user
     * @param string Name of common tag to place each line in
     * @param string Name of the root element. A root element should always be given.
     * @param string Encoding in which the data is provided
     * @return void  Prompts the user for a file download
     */
    public static function export_complex_table_xml ($data, $filename = 'export', $wrapper_tagname, $encoding = 'ISO-8859-1')
    {
        $file = api_get_path(SYS_ARCHIVE_PATH).'/'.uniqid('').'.xml';
        $handle = fopen($file, 'a+');
        fwrite($handle, '<?xml version="1.0" encoding="'.$encoding.'"?>'."\n");

        if (!is_null($wrapper_tagname)) {
            fwrite($handle, '<'.$wrapper_tagname.'>');
        }
        $s = self::_export_complex_table_xml_helper($data);
        fwrite($handle,$s);
        if (!is_null($wrapper_tagname)) {
            fwrite($handle, '</'.$wrapper_tagname.'>'."\n");
        }
        fclose($handle);
        DocumentManager :: file_send_for_download($file, true, $filename.'.xml');
        return false;
    }

    /**
     * Helper for the hierarchical XML exporter
     * @param   array   Hierarhical array composed of elements of type ('name'=>'xyz','value'=>'...')
     * @param   int     Level of recursivity. Allows the XML to be finely presented
     * @return string   The XML string to be inserted into the root element
     */
    public static function _export_complex_table_xml_helper ($data, $level = 1)
    {
        if (count($data) < 1) {
            return '';
        }
        $string = '';
        foreach ($data as $row) {
            $string .= "\n".str_repeat("\t",$level).'<'.$row['name'].'>';
            if (is_array($row['value'])) {
            	$string .= self::_export_complex_table_xml_helper($row['value'],$level+1)."\n";
                $string .= str_repeat("\t",$level).'</'.$row['name'].'>';
            } else {
                $string .= $row['value'];
                $string .= '</'.$row['name'].'>';
            }
        }

        return $string;
    }

    /**
     * @param array $data table to be read with the HTML_table class
     */
    public static function export_table_pdf($data, $params = array())
    {
        $table_html = self::convert_array_to_html($data, $params);
        $params['format'] = isset($params['format']) ? $params['format'] : 'A4';
        $params['orientation'] = isset($params['orientation']) ? $params['orientation'] : 'P';

        $pdf = new PDF($params['format'], $params['orientation'], $params);
        $pdf->html_to_pdf_with_template($table_html);
    }

    /**
     * @param string $html
     * @param array $params
     */
    public static function export_html_to_pdf($html, $params = array())
    {
        $params['format'] = isset($params['format']) ? $params['format'] : 'A4';
        $params['orientation'] = isset($params['orientation']) ? $params['orientation'] : 'P';

        $pdf = new PDF($params['format'], $params['orientation'], $params);
        $pdf->html_to_pdf_with_template($html);
    }

    /**
     * @param array $data
     * @param array $params
     *
     * @return string
     */
    public static function convert_array_to_html($data, $params = array())
    {
        $headers = $data[0];
        unset($data[0]);

        $header_attributes = isset($params['header_attributes']) ? $params['header_attributes'] : array();
        $table = new HTML_Table(array('class' => 'data_table', 'repeat_header' => '1'));
        $row = 0;
        $column = 0;
        foreach ($headers as $header) {
            $table->setHeaderContents($row, $column, $header);
            $attributes = array();
            if (isset($header_attributes) && isset($header_attributes[$column])) {
                $attributes = $header_attributes[$column];
            }
            if (!empty($attributes)) {
                $table->updateCellAttributes($row, $column, $attributes);
            }
            $column++;
        }
        $row++;
        foreach ($data as &$printable_data_row) {
            $column = 0;
            foreach ($printable_data_row as &$printable_data_cell) {
                $table->setCellContents($row, $column, $printable_data_cell);
                //$table->updateCellAttributes($row, $column, $atributes);
                $column++;
            }
            $table->updateRowAttributes($row, $row % 2 ? 'class="row_even"' : 'class="row_odd"', true);
            $row++;
        }
        $table_tp_html = $table->toHtml();
        return $table_tp_html;
    }
}
