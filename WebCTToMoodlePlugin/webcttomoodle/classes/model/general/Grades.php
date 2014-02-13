<?php
class GradeItem{

	public $id;// id="148">
	public $categoryid;//<categoryid>$@NULL@$</categoryid>
	public $itemname;//<itemname>1</itemname>
	public $itemtype;//<itemtype>/148/</itemtype>
	public $itemmodule;//<itemmodule>?</itemmodule>
	public $iteminstance;//<iteminstance>11</iteminstance>
	public $itemnumber;//<itemnumber>0</itemnumber>
	public $iteminfo;//<iteminfo>0</iteminfo>
	public $idnumber;//<idnumber>1</idnumber>
	public $calculation;//<calculation>0</calculation>
	public $gradetype;//<gradetype>0</gradetype>	
	public $grademax;//<grademax>100.00000</grademax>
	public $grademin;//<grademin>0.00000</grademin>
	public $scaleid;//<scaleid>$@NULL@$</scaleid>
	public $outcomeid;//<outcomeid>$@NULL@$</outcomeid>
	public $gradepass;//<gradepass>0.00000</gradepass>
	public $multfactor;//<multfactor>1.00000</multfactor>
	public $plusfactor;//<plusfactor>0.00000</plusfactor>
	public $aggregationcoef;//<aggregationcoef>0.00000</aggregationcoef>
	public $sortorder;//<sortorder>1</sortorder>
	public $display;//<display>0</display>
	public $decimals;//<decimals>$@NULL@$</decimals>
	public $hidden;//<hidden>0</hidden>
	public $locked;//<locked>0</locked>
	public $locktime;//<locktime>0</locktime>
	public $needsupdate;//<needsupdate>1</needsupdate>
	public $timecreated;//<timecreated>1392640698</timecreated>
	public $timemodified;//<timemodified>1392640698</timemodified>
	
	/**
	 * @param XMLWriter $writer
	 */
	public function toXML(&$writer){
		$writer->startElement("grade_item");
			$writer->writeAttribute("id", $this->id);
		
			$writer->writeElement("categoryid", $this->categoryid);
			$writer->writeElement("itemname", $this->itemname);
			$writer->writeElement("itemtype", $this->itemtype);
			$writer->writeElement("itemmodule", $this->itemmodule);
			$writer->writeElement("iteminstance", $this->iteminstance);
			$writer->writeElement("itemnumber", $this->itemnumber);
			$writer->writeElement("iteminfo", $this->iteminfo);
			$writer->writeElement("idnumber", $this->idnumber);
			$writer->writeElement("calculation", $this->calculation);
			$writer->writeElement("gradetype", $this->gradetype);			
			$writer->writeElement("grademax", $this->grademax);
			$writer->writeElement("grademin", $this->grademin);
			$writer->writeElement("scaleid", $this->scaleid);
			$writer->writeElement("outcomeid", $this->outcomeid);
			$writer->writeElement("gradepass", $this->gradepass);
			$writer->writeElement("multfactor", $this->multfactor);
			$writer->writeElement("plusfactor", $this->plusfactor);
			$writer->writeElement("aggregationcoef", $this->aggregationcoef);
			$writer->writeElement("sortorder", $this->sortorder);
			$writer->writeElement("display", $this->display);
			$writer->writeElement("decimals", $this->decimals);
			$writer->writeElement("hidden", $this->hidden);
			$writer->writeElement("locked", $this->locked);
			$writer->writeElement("locktime", $this->locktime);
			$writer->writeElement("needsupdate", $this->needsupdate);
			$writer->writeElement("timecreated", $this->timecreated);
			$writer->writeElement("timemodified", $this->timemodified);
			
		$writer->endElement();
	}
}

class GradeLetter{

}

