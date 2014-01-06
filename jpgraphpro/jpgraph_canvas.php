<?php
 class CanvasGraph extends Graph { function CanvasGraph($aWidth=300,$aHeight=200,$aCachedName="",$timeout=0,$inline=1) { $this->Graph($aWidth,$aHeight,$aCachedName,$timeout,$inline); } function InitFrame() { $this->StrokePlotArea(); } function Stroke($aStrokeFileName="") { if( $this->texts != null ) { for($i=0; $i < count($this->texts); ++$i) { $this->texts[$i]->Stroke($this->img); } } if( $this->iTables !== null ) { for($i=0; $i < count($this->iTables); ++$i) { $this->iTables[$i]->Stroke($this->img); } } $this->StrokeTitles(); $_csim = ($aStrokeFileName===_CSIM_SPECIALFILE); $this->iHasStroked = true; if( !$_csim ) { if( $this->iImgTrans ) { if( !class_exists('ImgTrans') ) { require_once('jpgraph_imgtrans.php'); } $tform = new ImgTrans($this->img->img); $this->img->img = $tform->Skew3D($this->iImgTransHorizon,$this->iImgTransSkewDist, $this->iImgTransDirection,$this->iImgTransHighQ, $this->iImgTransMinSize,$this->iImgTransFillColor, $this->iImgTransBorder); } if( $aStrokeFileName == _IMG_HANDLER ) { return $this->img->img; } else { $this->cache->PutAndStream($this->img,$this->cache_name,$this->inline,$aStrokeFileName); return true; } } } } ?>