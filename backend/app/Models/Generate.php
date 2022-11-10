<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Generate extends Model
{
    use HasFactory;


    private $sheet;
    private $spreadsheet;

    function __construct($spreadsheet)
    {
        $this->spreadsheet = $spreadsheet;
        $this->sheet = $spreadsheet->getActiveSheet();
    }

    public function getRecOfAnEmp(array $emp)
    {
        $empAccNum = array();
        for ($i = 1; $i <= count($emp); $i++) {
            if($i == 2400){
                break;
            }
            $empAccNum[] = $emp[$i][0];
        }

        $empUnique = array_unique($empAccNum);
        return array_keys($empUnique);
    }

    public function empCount(array $emp)
    {
        $empAccNum = array();
        for ($i = 1; $i < count($emp); $i++) {
            $empAccNum[] = $emp[$i][0];
        }
        $empUnique = array_unique($empAccNum);
        return count($empUnique);
    }

    public function generateCalendar($date, $row = 3)
    {
        $month = date("n", strtotime($date));
        $year = date("Y", strtotime($date));
        $daysOfWeek = array('H', 'B', 'C', 'D', 'E', 'F', 'G');
        $dayNames = array("Sun", 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
        $mergeF = array('B', 'C', 'D', 'E', 'F', 'G', 'H');
        // What is the first day of the month in question?
        $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
        // How many days does this month contain?
        $numberDays = date('t', $firstDayOfMonth);
        // Retrieve some information about the first day of the
        // month in question.
        $dateComponents = getdate($firstDayOfMonth);
        // What is the name of the month in question?
        $monthName = date("n", strtotime($date));
        // What is the index value (0-6) of the first day of the
        // month in question.
        $dayOfWeek = $dateComponents['wday'];

        // Initiate the day counter, starting with the 1st.

        $currentDay = 1;

        // The variable $dayOfWeek is used to
        // ensure that the calendar
        // display consists of exactly 7 columns.

        if ($dayOfWeek > 0) {
            // for merge if day is not in month start day
            for ($i = 0; $i <= $dayOfWeek; $i++) {
                $this->sheet->getStyle($mergeF[$i] . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('BDD7EE');
            }
        }
        $rowCount = 1;
        while ($currentDay <= $numberDays) {

            // Seventh column (Saturday) reached. Start a new row.

            if (strlen($monthName) == 1 & strlen((string)$currentDay) == 1) {
                $date = '0' . $monthName . '/' . "0" . $currentDay;
            } elseif (strlen($monthName) == 1 & strlen((string)$currentDay) != 1) {
                $date = '0' . $monthName . '/' . $currentDay;
            } elseif (strlen($monthName) == 2 & strlen((string)$currentDay) != 1) {
                $date = $monthName . '/' . $currentDay;
            } elseif (strlen($monthName) == 2 & strlen((string)$currentDay) == 1) {
                $date = $monthName . '/' . '0' . $currentDay;
            }

            if ($dayOfWeek == 7) {
                $dayOfWeek = 0;
                //new row
                $row += 2;
                $rowCount++;
            }
            if ($dayOfWeek != 0) {
                $this->sheet->setCellValue($daysOfWeek[$dayOfWeek] . $row, $date . " " . $dayNames[$dayOfWeek]);
                $this->sheet->getStyle($daysOfWeek[$dayOfWeek] . $row)->getFont()->setSize(8);
                $this->sheet->getStyle($daysOfWeek[$dayOfWeek] . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('BDD7EE');
                if ($dayOfWeek == 6) {
                    $this->sheet->getStyle($daysOfWeek[$dayOfWeek] . $row)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FF5050');
                }
            } else {
                $row -= 2;
                $this->sheet->setCellValue($daysOfWeek[$dayOfWeek] . $row, $date . " " . $dayNames[$dayOfWeek]);
                $this->sheet->getStyle($daysOfWeek[$dayOfWeek] . $row)->getFont()->setSize(8);
                $this->sheet->getStyle($daysOfWeek[$dayOfWeek] . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF5050');
                $row += 2;
            }

            // Increment counters

            $currentDay++;
            $dayOfWeek++;

        }

        // Complete the row of the last week in month, if necessary

        if ($dayOfWeek != 7) {

            $remainingDays = 7 - $dayOfWeek;
            for ($i = 1; $i < 6; $i++) {
                $this->sheet->getStyle($daysOfWeek[$i] . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('BDD7EE');
            }
            $this->sheet->getStyle($daysOfWeek[0] . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF5050');
            $this->sheet->getStyle($daysOfWeek[6] . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF5050');


        }
        $this->sheet->getStyle('H' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF5050');
        return $rowCount;
    }

    public function generateEmployeeReport($date, array $emp, array $recs, $count, $rowCount, $row = 2)
    {
        $month = date("n", strtotime($date));
        $year = date("Y", strtotime($date));
        $daysOfWeek = array('H', 'B', 'C', 'D', 'E', 'F', 'G');
        // What is the first day of the month in question?
        $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
        // How many days does this month contain?
        $numberDays = date('t', $firstDayOfMonth);
        // Retrieve some information about the first day of the
        // month in question.
        $dateComponents = getdate($firstDayOfMonth);
        // What is the name of the month in question?
        $monthName = date("n", strtotime($date));
        // What is the index value (0-6) of the first day of the
        // month in question.
        $dayOfWeek = $dateComponents['wday'];

        // Initiate the day counter, starting with the 1st.

        $currentDay = 1;

        // The variable $dayOfWeek is used to
        // ensure that the calendar
        // display consists of exactly 7 columns.


        $i = 1;
        $pos = 2;
        $dayIncrement = 1;
        $next = $recs[1];
        $previous = 1;
        while ($dayIncrement <= $numberDays * $count) {

            // Seventh column (Saturday) reached. Start a new row.
            $fingerPrintDate = $monthName . '/' . $currentDay . "/" . $year;


            if ($dayOfWeek == 7) {
                $dayOfWeek = 0;
                //new row
                $row += 2;
            }
            $this->sheet->setCellValue("A" . $pos, $i);
            $this->sheet->getStyle("A" . $pos)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('7A0000');
            $this->sheet->getStyle("A" . $pos)
                ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
            $this->sheet->setCellValue("B" . $pos, $emp[$previous + 1][1]);
            $this->sheet->mergeCells('B' . $pos . ':H' . $pos);
            $this->sheet->getStyle('B' . $pos)->getFont()->setSize(9);
            $this->sheet->getStyle('B' . $pos)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('0E5970');
            $this->sheet->getStyle("B" . $pos)
                ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

            $a = $previous;
            while ($previous <= $next) {
                if (strtotime($emp[$previous][4]) == strtotime($fingerPrintDate)) {
                    //if ($dayOfWeek != 0 & $dayOfWeek != 6) {
                    $row++;
                    if ($emp[$previous][4] == $emp[$previous + 1][4]) {
                        $fingerPrint = substr($emp[$previous][2], 0, 5) . ' - ' . substr($emp[$previous][3], 0, 5) . ' '
                            . substr($emp[$previous + 1][2], 0, 5) . ' - ' . substr($emp[$previous + 1][3], 0, 5);
                        $previous += 2;
                    } else {
                        $fingerPrint = substr($emp[$previous][2], 0, 5) . ' - ' . substr($emp[$previous][3], 0, 5);
                        $previous += 1;
                    }
                    if ($dayOfWeek != 0 & $dayOfWeek != 6) {
                        $this->sheet->setCellValue($daysOfWeek[$dayOfWeek] . $row, $fingerPrint);
                        $this->sheet->getStyle($daysOfWeek[$dayOfWeek] . $row)->getFont()->setSize(7);
                    }
                    $row--;

                } else {
                    $previous += 1;
                }
            }
            $previous = $a;


            // Increment counters
            $dayIncrement++;
            $currentDay++;
            $dayOfWeek++;
            if ($currentDay > $numberDays) {
                $row += 3;
                $previous = $recs[$i];
                if ($i < $count) {
                    if($i+1 == 70){
                        break;
                    }
                    $next = $recs[$i + 1];
                }
                $i++;
                $currentDay = 1;
                $dayOfWeek = $dateComponents['wday'];
                if ($numberDays == 31 && $rowCount > 5) {
                    $pos += 13;
                } else {
                    $pos += 11;
                }
            }
            if ($i == $count + 1) {
                break;
            }

        }

        // Complete the row of the last week in month, if necessary


    }

    public function header($monthName, $year, $c)
    {
        $this->spreadsheet->getActiveSheet()->getSheetView()->setZoomScale(170);
        $this->spreadsheet->getDefaultStyle()->getFont()->setName('ËÎÌå');
        $this->sheet->mergeCells('A1:H1');
        $this->spreadsheet->getActiveSheet()->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('2F75B5');
        $this->spreadsheet->getActiveSheet()->getStyle('A')->getFont()->setName('Calibri');
        $this->spreadsheet->getActiveSheet()->getStyle('A1')->getFont()->setSize(22);
        $this->sheet->setCellValue('A1', 'Finger Print Report for ' . $monthName . '-' . $year);
        $this->spreadsheet->getActiveSheet()->getStyle('A1')
            ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        $this->spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(69);
        $this->spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(2.5833);
        $this->sheet->getStyle('A:H')->getAlignment()->
        setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $this->sheet->getStyle('A:H')->getAlignment()->
        setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $this->sheet->getStyle('A:H')->getAlignment()->setWrapText(true);
        $this->sheet->setCellValue('A2', '1');
        $this->sheet->mergeCells('B2:H2');
        $this->sheet->setCellValue('B2', $c[1][1]);
    }


}
