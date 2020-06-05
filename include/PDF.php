<?php
require('fpdf/fpdf.php');

class PDF extends FPDF{

  function Table($header, $data)
  {
      // Column widths
      $w = array(10, 36, 16, 16, 120);

      // Header
      for($i=0;$i<count($header);$i++){
        $this->Cell($w[$i], 7, $header[$i], 1, 0 ,'C');
      }
      $this->Ln();

      // Data
      foreach($data as $row)
      {
          $h = 6 * substr_count($row[1], "\n") + 6;
          $this->Cell($w[0], $h, $row[0], 'LRBT');
          $this->Cell($w[1], $h, $row[4], 'LRBT');
          $this->Cell($w[2], $h, $row[2], 'LRBT');
          $this->Cell($w[3], $h, $row[3], 'LRBT');
          $this->MultiCell($w[4], 6, $row[1], 'LRBT');
          $this->Ln();
      }
      // Closing line
      $this->Cell(array_sum($w),0,'','T');
  }

  function DocsTable($header, $data)
  {
      // Column widths
      $w = 160;
      $this->SetFillColor(120);

      // Data
      foreach($data as $row)
      {

        $this->Cell($w, 12, $row[2], 'T', 1);

        // HTTP method
        $this->Cell($w, 7, $header[0], 1, 1, "", 1);
        $this->Cell($w, 6, $row[1], 1, 1);

        // API method
        $this->Cell($w, 7, $header[1], 1, 1, "", 1);
        $this->Cell($w, 6, $row[2], 1, 1);

        // Parameters
        $this->Cell($w, 7, $header[2], 1, 1, "", 1);
        $this->MultiCell($w, 6, $row[3], 1, 1);

        // Response
        $this->Cell($w, 7, $header[3], 1, 1, "", 1);
        $this->MultiCell($w, 6, $row[4], 1, 1);

        // Description
        $this->Cell($w, 7, $header[4], 1, 1, "", 1);
        $this->MultiCell($w, 6, $row[5], 1, 1);

        $this->Ln();
        $this->Cell($w, 0, '', 'T', 1);
      }
      // Closing line
      $this->Cell($w, 0, '', 'T');
  }


};

?>
