<?php
error_reporting(E_ALL & ~E_DEPRECATED);
require 'vendor/autoload.php';
require_once 'db_connect.php';

class PDF extends FPDF
{
    protected $B = 0;
    protected $I = 0;
    protected $U = 0;
    protected $HREF = '';
    protected $isFirstPage = true;

    function Header()
    {
        $this->SetFont('Arial', 'B', 20);
        $this->SetFillColor(52, 152, 219);
        $this->SetTextColor(255);
        $this->Cell($this->GetPageWidth(), 30, utf8_decode('Workshop-Bericht'), 0, 1, 'C', true);
        $this->Ln(10); // Zusätzlicher Abstand nach dem Titel
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Seite ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function ChapterTitle($num, $label)
    {
        if (!$this->isFirstPage) {
            $this->AddPage();
        }
        $this->isFirstPage = false;
        
        $this->SetFont('Arial', 'B', 16);
        $this->SetFillColor(52, 152, 219);
        $this->SetTextColor(255);
        $this->Cell($this->GetPageWidth(), 10, utf8_decode("Phase $num: $label"), 0, 1, 'L', true);
        $this->Ln(4);
    }

    function ChapterBody($body)
    {
        $this->SetFont('Arial', '', 11);
        $this->SetTextColor(0);
        $this->MultiCell(0, 6, utf8_decode($body));
        $this->Ln();
    }

    function SubTitle($title)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(52, 152, 219);
        $this->Cell(0, 6, utf8_decode($title), 0, 1, 'L');
        $this->Ln(2);
    }

    function TaskItem($text, $checked, $notes = '')
    {
        $startY = $this->GetY();
        $startX = $this->GetX();
        $pageWidth = $this->GetPageWidth();
        $marginRight = $this->rMargin;
        $boxWidth = $pageWidth - $startX - $marginRight;
        
        $this->SetFillColor(245, 245, 245);
        $this->Rect($startX, $startY, $boxWidth, 5, 'F');
        
        // Checkbox
        $this->SetDrawColor(52, 152, 219);
        $this->Rect($startX + 2, $startY + 2, 6, 6);
        if ($checked) {
            $this->SetFillColor(52, 152, 219);
            $this->Rect($startX + 3, $startY + 3, 4, 4, 'F');
        }
        
        // Aufgabentext
        $this->SetXY($startX + 10, $startY + 1);
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(0);
        $this->Write(5, utf8_decode($text));
        
        $textEndY = $this->GetY() + 5;
        
        // Notizen mit HTML-Interpretation
        if (!empty($notes)) {
            $this->SetXY($startX + 10, $textEndY);
            $this->SetFont('Arial', '', 10);
            $this->SetTextColor(100);
            $this->WriteHTML(utf8_decode($notes));
        }
        
        $endY = $this->GetY();
        
        // Zeichne den grauen Hintergrund
        $this->SetFillColor(245, 245, 245);
        $this->Rect($startX, $startY, $boxWidth, $endY - $startY + 2, 'F');
        
        // Zeichne den Inhalt erneut
        $this->SetXY($startX, $startY);
        
        // Checkbox
        $this->SetDrawColor(52, 152, 219);
        $this->Rect($startX + 2, $startY + 2, 6, 6);
        if ($checked) {
            $this->SetFillColor(52, 152, 219);
            $this->Rect($startX + 3, $startY + 3, 4, 4, 'F');
        }
        
        // Aufgabentext
        $this->SetXY($startX + 10, $startY + 1);
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(0);
        $this->Write(5, utf8_decode($text));
        
        // Notizen mit HTML-Interpretation
        if (!empty($notes)) {
            $this->SetXY($startX + 10, $textEndY);
            $this->SetFont('Arial', '', 10);
            $this->SetTextColor(100);
            $this->WriteHTML(utf8_decode($notes));
        }
        
        $this->SetY($endY + 4);
    }

    function WriteHTML($html)
    {
        $html = str_replace("\n", ' ', $html);
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach($a as $i=>$e) {
            if($i%2==0) {
                $this->Write(5, $e);
            } else {
                if($e[0]=='/') {
                    $this->CloseTag(strtoupper(substr($e, 1)));
                } else {
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    foreach($a2 as $v) {
                        if(preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3))
                            $attr[strtoupper($a3[1])] = $a3[2];
                    }
                    $this->OpenTag($tag, $attr);
                }
            }
        }
    }

    function OpenTag($tag, $attr)
    {
        if($tag=='B' || $tag=='STRONG')
            $this->SetStyle('B', true);
        if($tag=='I' || $tag=='EM')
            $this->SetStyle('I', true);
        if($tag=='U')
            $this->SetStyle('U', true);
        if($tag=='BR')
            $this->Ln(5);
        if($tag=='P')
            $this->Ln(7);
    }

    function CloseTag($tag)
    {
        if($tag=='B' || $tag=='STRONG')
            $this->SetStyle('B', false);
        if($tag=='I' || $tag=='EM')
            $this->SetStyle('I', false);
        if($tag=='U')
            $this->SetStyle('U', false);
        if($tag=='P')
            $this->Ln(7);
    }

    function SetStyle($tag, $enable)
    {
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach(array('B', 'I', 'U') as $s) {
            if($this->$s > 0)
                $style .= $s;
        }
        $this->SetFont('', $style);
    }
}

// Funktion zum Abrufen aller Phasen mit ihren Aufgaben
function getAllPhases($pdo) {
    $stmt = $pdo->query("SELECT * FROM phases ORDER BY id");
    $phases = $stmt->fetchAll();

    foreach ($phases as &$phase) {
        $stmt = $pdo->prepare("SELECT * FROM subtasks WHERE phase_id = ? ORDER BY id");
        $stmt->execute([$phase['id']]);
        $phase['subtasks'] = $stmt->fetchAll();
    }

    return $phases;
}

// Daten abrufen
$phases = getAllPhases($pdo);

// PDF erstellen
$pdf = new PDF();
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AliasNbPages();
$pdf->AddPage();

foreach ($phases as $index => $phase) {
    $pdf->ChapterTitle($index + 1, $phase['name']);
    
    $pdf->SubTitle('Themenblock:');
    $pdf->ChapterBody($phase['themenblock']);
    
    $pdf->SubTitle('Teilnehmerkreis:');
    $pdf->ChapterBody($phase['teilnehmerkreis']);
    
    $pdf->SubTitle('Vorbereitung:');
    $pdf->ChapterBody($phase['vorbereitung']);
    
    $pdf->SubTitle('Dauer:');
    $pdf->ChapterBody($phase['dauer'] . ' Minuten');
    
    $pdf->SubTitle('Aufgaben & Diskussionspunkte:');
    foreach ($phase['subtasks'] as $subtask) {
        $pdf->TaskItem($subtask['text'], $subtask['completed'], $subtask['notes']);
    }
}

// PDF ausgeben
$pdf->Output('Workshop-Bericht.pdf', 'D');
