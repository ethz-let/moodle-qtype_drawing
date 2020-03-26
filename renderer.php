<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * drawing question renderer class.
 *
 * @package	qtype
 * @subpackage drawing
 * @copyright ETHZ LET <amr.hourani@id.ethz.ch>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for drawing questions.
 *
 * @copyright  ETHZ LET <amr.hourani@id.ethz.chh>
 * @license	http://opensource.org/licenses/BSD-3-Clause
 */
class qtype_drawing_renderer extends qtype_renderer {


	public static function translate_to_js() {
		global $PAGE;
		foreach (array_keys(get_string_manager()->load_component_strings('qtype_drawing', current_language())) as $string) {
			$PAGE->requires->string_for_js($string, 'qtype_drawing');
		}
	}

	public static  function strstr_after($haystack, $needle, $case_insensitive = false) {
		$strpos = ($case_insensitive) ? 'stripos' : 'strpos';
		$pos = $strpos($haystack, $needle);
		if (is_int($pos)) {
			return substr($haystack, $pos + strlen($needle));
		}
		// Most likely false or null
		return $pos;
	}

	private static function create_gd_image_from_string($imgString) {
		return  '';
		if ($imgString != '') {
			$imgData = base64_decode(self::strstr_after($imgString, 'base64,'));
			$img =  imagecreatefromstring($imgData);

			// integer representation of the color black (rgb: 0,0,0)
			$background = imagecolorallocate($img, 0, 0, 0);
			// removing the black from the placeholder
			imagecolortransparent($img, $background);

			imagealphablending( $img, false );
			imagesavealpha( $img, true );
			return $img;
		}
	}

	public static function compare_drawings($teacherAnswer, $studentAnswer, $createBlendedImg = false) {
	return 0;
		// Beginning of dataURL string: "data:image/png;base64,".
		core_php_time_limit::raise();
		raise_memory_limit(MEMORY_EXTRA);

		$onlyShowCorrectAnswer = true;

		if ($studentAnswer != '') {
			// no answer given by student--that's fine, we can still show the right answer.
			$onlyShowCorrectAnswer = false;
			$currentAnswerImg = self::create_gd_image_from_string($studentAnswer);
			$studentAnswerImg = $currentAnswerImg;
		}

		$correctAnswerImg = self::create_gd_image_from_string($teacherAnswer);

		$width = imagesx($correctAnswerImg);
		$height = imagesy($correctAnswerImg);

		if ($createBlendedImg ===  true) {
			return 0;
			// Create a copy just to have somewhere to write to. It doesn't matter that the $teacherAnswer is not blank
			// we don't need blank, since in fact more pixels than the ones in the teacher answer picture are going to be drawn into.
			$blendedImg = self::create_gd_image_from_string($teacherAnswer);
			$green = imagecolorallocate($blendedImg, 0, 255, 0);
			$blue = imagecolorallocate($blendedImg, 0, 0, 255);
			$red = imagecolorallocate($blendedImg, 255, 0, 0);

		}

		$matchingPixels = 0;
		$matchPercentage = 0;
		$teacherOnlyPixels = 0;
		$studentOnlyPixels = 0;
		$totalPixels = 0;
		$allotherpixels = 0;

		// *************
		// WARNING: THE FOLLOWING IS SPAHGETTI CODE FOR OPTIMIZATION PURPOSES. SORRY.
		// *************


		if (!$onlyShowCorrectAnswer) {
			if ($createBlendedImg ===  true) {

				// Start of Rocket Science to fix spaghetti

				// Copy and merge
				$dest_image = imagecreatetruecolor($width, $height);

				imagealphablending( $dest_image, false );
				//make sure the transparency information is saved
				imagesavealpha($dest_image, true);

				//create a fully transparent background (127 means fully transparent)
				$trans_background = imagecolorallocatealpha($dest_image, 0, 0, 0, 127);

				//fill the image with a transparent background
				imagefill($dest_image, 0, 0, $trans_background);

				//$correctAnswerImg = self::create_gd_image_from_string($teacherAnswer);
				//$studentAnswerImg = self::create_gd_image_from_string($studentAnswer);

				$ETHzLogoURI = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAALCAYAAAA9St7UAAAACXBIWXMAAC4jAAAuIwF4pT92AAADuklEQVR42p2WT4iVZRTGf895751R01ExJEFSEAwqbVLBoqm9USgtWkVQ0cIicFHpxgKhXbXJhS2CqE2gi7JFQYL9gbDCK/gnmCQxxoKpaWRwJu/M3O88Le43dh3GSt/N933nz/ue57zPOedTKYxw/RJg/n312qh++n/Y3sz3DeOwQbpe35hzzuSixDC3th6WmLH5rj5oewTLewD2HnrBZonEVZt+iTXzg7eZsTkTwdb5AG0S+AF4oFfX6EH5TqY/ugUQQ6XoEeCLqvIeYLgUtedltyez+lLykM0Jmy01EM8DMypxAti2gO73Ungrk8O9l3QNSH8/pwCazeY6u7PtOm6I2dlZHwUopWyVcv3AQBwfH6/GJf1ZCosydbJrq6EITs6j21w2AT8jgcRG6R+AmXQkOhL9EncCL84Fb/MX0C8RwOmq4j7gKtBXy7o3YtNut72r+95Zn8nd9f6rgZ3QBdnV5+vA4Ph4tX0uyKriXTuPdRMRWzIzMmnZvFkKmpiY/HbZsqVHStHhqvKTElsyOQSslNiVyYMR3pSp0QgOZnIwxPMK1tjsycS2x0qJDzJ9EvgK+An4TeLjCJZSCiMSR73AajR4rBQuSbxWix6KYLgUTtt+byEfYG8pjJTCpbqR7I7g1VIYsb1Z4sdSGGk0tEPi8wgdsz0YwcUIfql97ongfCk8IfF+HePZWjcEfNKVaUcpfFoKIw1AEWoBjwN7Gw1tziQksNkXgUuhBRARoxEsyeR74FlgLEIfRmiy08n9XRttrRllm0MRzEi8lElbUieCZQBVxXmJjeAjpehRiUbtc8V2M0LRbHJudpa3u/uy3CYljUoe7Dao/LUUbZAgujx0a446pfCGzYFMDkQw25Vlq8t/b6k3PQUwNTW1BthpX6u15yTfXdfEYWAY2N8tHX5uNLSuTtCFvj4NSDQjdErq7U4622jEJolqepqQKDX9ZyVGbK+VCJurkjoSywAFwMxM1QLGgFemp/217W9sr8rkDpvL7bae6mafbTZUFS1gbGBgYHMEfZmcBMaazeYxYIXNmYmJyZczMbA3k+M2a6Gcy9S+TF7odHxvXebn7Gs1CbiV6UGJxcBdVcXuquJp0GdVRasU7q+BnQc2zDmpyzuN2c55naoAq2w6wHgtXiHRB1zOZAZYXF/5ZZvpCBYBy23aNhP10AIYkFhsc0XiD5vVQNTBXgZW9oyBybpDNW3SZiSCpcDtNlNzfjZtoCNxG0ANZMHpfCOZ/+MPQDcx5W80tRea8Dc6W4D/BnxA+7bYujrLAAAAAElFTkSuQmCC";
				$ETHzLogoGD = self::create_gd_image_from_string($ETHzLogoURI);

				//copy each png file on top of the destination (result) png
				imagecopy($dest_image, $correctAnswerImg, 0, 0, 0, 0, $width, $height);
				imagecopy($dest_image, $studentAnswerImg, 0, 0, 0, 0, $width, $height);
				imagecopy($dest_image , $ETHzLogoGD, $width - 50, $height - 11, 0, 0, 50, 11);

				$yellowgreen = imagecolorallocate($dest_image, 221, 253, 11);
				$pixel_correct_answer = array();
				$totalpix = $width * $height;
				for ($x = 0; $x < $width; $x++) {
					for ($y = 0; $y < $height; $y++) {
						$totalPixels++;
						/*
						//match
						if (((imagecolorat($correctAnswerImg, $x, $y) >> 24) & 0xFF) !== 127) {
							if (((imagecolorat($studentAnswerImg, $x, $y) >> 24) & 0xFF) !== 127) {
								$color = imagecolorallocate($dest_image,rand(0,255), rand(0,255), rand(0,255));
								imagesetpixel($dest_image,  $x, $y, $color);
								$matchingPixels++;
							}
						}
						*/
						// teacher only
						if (((imagecolorat($correctAnswerImg, $x, $y) >> 24) & 0xFF) !== 127) {
							if (((imagecolorat($studentAnswerImg, $x, $y) >> 24) & 0xFF) === 127) {
								$rgb = imagecolorat($correctAnswerImg, $x, $y);
								$r = ($rgb >> 16) & 0xFF;
								$g = ($rgb >> 8) & 0xFF;
								$b = $rgb & 0xFF;



								$color = imagecolorallocatealpha($dest_image,$r, $g, $b,60);
								imagesetpixel($dest_image,  $x, $y, $color);
								$teacherOnlyPixels++;
							}
						}
						// student only
						if (((imagecolorat($correctAnswerImg, $x, $y) >> 24) & 0xFF) === 127) {
							if (((imagecolorat($studentAnswerImg, $x, $y) >> 24) & 0xFF) !== 127) {

								$studentOnlyPixels++;
							}
						}
						 /*
						 else if (((imagecolorat($correctAnswerImg, $x, $y) >> 24) & 0xFF) !== 127 && !((imagecolorat($studentAnswerImg, $x, $y) >> 24) & 0xFF) !== 127) {
							$teacherOnlyPixels++;
						} else if (!((imagecolorat($correctAnswerImg, $x, $y) >> 24) & 0xFF) !== 127 && ((imagecolorat($studentAnswerImg, $x, $y) >> 24) & 0xFF) !== 127) {
							$studentOnlyPixels++;
						}else{
							$allotherpixels++;
						}*/

					}
				}


					if (!$onlyShowCorrectAnswer) {
						imagedestroy($studentAnswerImg);
						//avoid dividing by zero..
						$dividedpixels = $totalPixels; // $matchingPixels + $teacherOnlyPixels + $studentOnlyPixels;
						if($dividedpixels == 0){
							$matchPercentage = 0;
						}else{
							$matchPercentage = ($matchingPixels / $dividedpixels)*100;
						}
					}

					$imgresult = self::gdimage_to_datauri($dest_image);

					//destroy all the image resources to free up memory
					@imagedestroy($correctAnswerImg);
					@imagedestroy($studentAnswerImg);
					@imagedestroy($dest_image);//print_error($matchingPixels + $teacherOnlyPixels + $studentOnlyPixels);


					return array($imgresult,($matchingPixels / ($matchingPixels + $teacherOnlyPixels + $studentOnlyPixels))*100);			//$matchPercentage


					// end of Rocket Science to fix spaghetti


			} else {
				// DO NO CREATE BLENDED IMAGE
				for ($x = 0; $x < $width; $x++) {
					for ($y = 0; $y < $height; $y++) {
						if (((imagecolorat($correctAnswerImg, $x, $y) >> 24) & 0xFF) !== 127 && ((imagecolorat($currentAnswerImg, $x, $y) >> 24) & 0xFF) !== 127) {

							$matchingPixels++;

						} else if (((imagecolorat($correctAnswerImg, $x, $y) >> 24) & 0xFF) !== 127 && ((imagecolorat($currentAnswerImg, $x, $y) >> 24) & 0xFF) === 127) {

							$teacherOnlyPixels++;

						} else if (((imagecolorat($correctAnswerImg, $x, $y) >> 24) & 0xFF) === 127 && ((imagecolorat($currentAnswerImg, $x, $y) >> 24) & 0xFF) !== 127) {

							$studentOnlyPixels++;
						}

					}
				}
				// --- DO NOT CREATE BLENDED IMAGE
			}
		} else {
			if ($createBlendedImg ===  true) {
				for ($x = 0; $x < $width; $x++) {
					for ($y = 0; $y < $height; $y++) {
						// ONLY SHOW CORRECT ANSWER -- NO INPUT FROM USER
						if (((imagecolorat($correctAnswerImg, $x, $y) >> 24) & 0xFF) !== 127) {

							$teacherOnlyPixels++;

							imagesetpixel($blendedImg, $x, $y, $blue);

						}

					}
				}
			} else {
				// DO NOT CREATE BLENDED IMAGE
				for ($x = 0; $x < $width; $x++) {
					for ($y = 0; $y < $height; $y++) {
						// ONLY SHOW CORRECT ANSWER -- NO INPUT FROM USER
						if (((imagecolorat($correctAnswerImg, $x, $y) >> 24) & 0xFF) !== 127) {

							$teacherOnlyPixels++;
						}

					}
				}
				// --- DO NOT CREATE BLENDED IMAGE
			}

		}

		imagedestroy($correctAnswerImg);

		if (!$onlyShowCorrectAnswer) {
			imagedestroy($currentAnswerImg);
			//avoid dividing by zero..
			$dividedpixels = $matchingPixels + $teacherOnlyPixels + $studentOnlyPixels;
			if($dividedpixels == 0){
				$matchPercentage = 0;
			}else{
				$matchPercentage = ($matchingPixels / $dividedpixels)*100;
			}
		}

		if ($createBlendedImg ===  true) {

			$blendedImgDataURL = self::gdimage_to_datauri($blendedImg);
			imagedestroy($blendedImg);
			return array($blendedImgDataURL, $matchPercentage);
		}

		//free up memory by chancing on any image that might have been created!
		@imagedestroy($studentAnswerImg);
		@imagedestroy($currentAnswerImg);
		@imagedestroy($blendedImg);
		@imagedestroy($correctAnswerImg);


		return $matchPercentage;
	}
	private static function isBlue($array) {
		if ($array[0] == 0 && $array[1] == 0 && $array[2] == 255) {
			return true;
		}
		return false;
	}

	public static function gdimage_to_datauri($gdImage) {

		ob_start();
		imagepng($gdImage);
		$ImgData = ob_get_contents();
		ob_end_clean();


		stream_wrapper_register("BlobDataAsFileStream", "drawing_blob_data_as_file_stream");

		//Store $swf_blob_data to the data stream
		drawing_blob_data_as_file_stream::$blob_data_stream = $ImgData;

		//Run getimagesize() on the data stream
		$image_size = getimagesize('BlobDataAsFileStream://');

		stream_wrapper_unregister("BlobDataAsFileStream");

		$imgdatauri = 'data:' . $image_size['mime'] . ';base64,' . base64_encode($ImgData);
		return $imgdatauri;
	}

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {

    	global $CFG, $DB, $PAGE;
    	// A unique instance id for this particular canvas presentation. Will help refer back to it afterwards.
    	$canvasinstanceid = uniqid();

			$question = $qa->get_question();
			$canvasinfo = $DB->get_record('qtype_drawing', array('questionid' => $question->id));
			$currentAnswer = $qa->get_last_qt_var('answer');
			$inputname = $qa->get_qt_field_name('answer');
			$background = self::get_image_for_question($question);
			$studentanswer = $qa->get_last_qt_var('answer');
			$qattempt = $qa->get_database_id();
	  	qtype_drawing_renderer::translate_to_js();
			if(!empty($background) && !$options->readonly){
				$this->page->requires->yui_module('moodle-qtype_drawing-form', 'Y.Moodle.qtype_drawing.form.attemptquestion', array($question->id, $background[1], $canvasinfo->backgroundwidth, $canvasinfo->backgroundheight,$background[0] ));
			}
			$canvas = "<div class=\"qtype_drawing_id_" . $question->id ."\" data-canvas-instance-id=\"$canvasinstanceid\" id=\"qtype_drawing_attr_id_" . $question->id ."\">";

			if ($options->readonly) {
				$readonlyCanvas = ' readonly-canvas';
			} else {
				$readonlyCanvas = '';
				$inputnamelastsaved = $inputname.'_lastsaved';
				$canvas .= "<textarea class=\"qtype_drawing_textarea\" name=\"$inputname\" id=\"qtype_drawing_textarea_id_".$question->id."\" style=\"display:none\">$currentAnswer</textarea><textarea class=\"qtype_drawing_textarea\" name=\"$inputnamelastsaved'\" id=\"qtype_drawing_last_saved_answer_id_".$question->id."\" style=\"display:none\">".trim(str_replace(array('\n\r', '\n', '\r'), '', $currentAnswer))."</textarea><input type=\"hidden\" name=\"qtype_drawing_drawingevent\" id=\"qtype_drawing_drawingevent_".$question->id."\" value=\"\">";
			}

			if($readonlyCanvas && $readonlyCanvas != '') {
			/*
			$tempfile = 'qtype_drawing_'.$canvasinstanceid.'_'.$qattempt.'_'.$question->id.'.png';
			$tempfile1 = '1qtype_drawing_'.$canvasinstanceid.'_'.$qattempt.'_'.$question->id.'.png';
			$tempfullpath = $CFG->tempdir.'/'.$tempfile;
			$tempfullpath1 = $CFG->tempdir.'/'.$tempfile1;
			$sesskey = sesskey();

			$image = new Imagick();
			$image->setBackgroundColor(new ImagickPixel('transparent'));
			$image->readImageBlob($studentanswer);
			$image->setImageFormat("png24");
			$image->writeImage($tempfullpath);

			$image->clear();
			$image->destroy();

			$studentmergedanswer = "<img src='$CFG->wwwroot/question/type/drawing/image.php?sesskey=$sesskey&i=$tempfile' style='background: url($background);background-repeat: no-repeat; background-size: $canvasinfo->backgroundwidth"."px $canvasinfo->backgroundheight"."px;'/>";
			*/
				if($background[0] == 'svg') {
					$finalbackground = 'data:image/svg+xml,'.rawurlencode($background[1]);
				} else {
					$finalbackground = $background[1];
				}
				$studentmergedanswer = str_replace('<svg',"<svg style='background-image: url($finalbackground);background-repeat: no-repeat; background-size: $canvasinfo->backgroundwidth"."px $canvasinfo->backgroundheight"."px;'",$studentanswer);

				$canvas .=  '
							 <div class="qtype_drawing_drawingwrapper" id ="qtype_drawing_drawingwrapper_'.$question->id.'" style="min-height:'.$canvasinfo->backgroundheight.'px; min-width:'.$canvasinfo->backgroundwidth.'px">'.$studentmergedanswer.'</div>';
			} else {
				$canvas .=  '
					<script type="text/javascript" src="'.$CFG->wwwroot.'/question/type/drawing/js/embedapi.js"></script>
					<script type="text/javascript">
					svgCanvas = null;
					function init_qtype_drawing_embed(qid) {
							var frame = document.getElementById("qtype_drawing_editor_"+qid);
							svgCanvas = new embedded_svg_edit(frame);
					  	svgCanvas.setHDQuestionID(qid);
						//	 frame.style = "height:" + frame.contentWindow.document.body.scrollHeight + "px; width: " + frame.contentWindow.document.body.offsetWidth + "px" ;
							var drawingwrapper = document.getElementById("qtype_drawing_drawingwrapper_"+qid);
							drawingwrapper.style = "height:"+frame.contentWindow.document.body.offsetHeight + "px";


					}
					</script>


					<script type="text/javascript">
							//<![CDATA[
									YUI().use("node", "event", function(Y) {
											var doc = Y.one("body");
											var drawing_iframeid = "#qtype_drawing_editor_"+'.$question->id.';
											var drawing_toggle_btn = "#qtype_drawing_togglebutton"+'.$question->id.';

											var frame = Y.one("#qtype_drawing_editor_"+'.$question->id.');
											var padding = 150;
											var lastHeight;
											var resize = function(e) {
											var viewportHeight = doc.get("winHeight");
											if(lastHeight !== Math.min(doc.get("docHeight"), viewportHeight)){
													if(viewportHeight <= 500 ) viewportHeight = 650;
															Y.one("#qtype_drawing_editor_"+'.$question->id.').setStyle("height", viewportHeight + "px");
                                                            Y.one("#qtype_drawing_drawingwrapper_"+'.$question->id.').setStyle("height", viewportHeight +"px");
															lastHeight = Math.min(doc.get("docHeight"), doc.get("winHeight"));
													}
											};
											resize();

											Y.on("windowresize", resize);
											var quiz_is_timed = 0;
											Y.one("#qtype_drawing_togglebutton_id_"+'.$question->id.').on("click", function (e) {
											Y.one("#qtype_drawing_drawingwrapper_"+'.$question->id.').toggleClass("qtype_drawing_maximized");
											Y.one("#qtype_drawing_editor_'.$question->id.'").set("height","100%");
											if (Y.one("#qtype_drawing_drawingwrapper_"+'.$question->id.').hasClass("qtype_drawing_maximized")) {
												Y.one("#qtype_drawing_drawingwrapper_"+'.$question->id.').setStyle("margin-top", "0px");
                                                var viewportHeight = Y.one("#qtype_drawing_editor_"+'.$question->id.').getStyle("height");
												Y.one("#qtype_drawing_drawingwrapper_"+'.$question->id.').setStyle("height", viewportHeight  +"px");
												Y.one("#qtype_drawing_drawingwrapper_'.$question->id.'").set("height","100%");
												Y.one("#qtype_drawing_editor_"+'.$question->id.').setStyle("height", "100%");
												Y.one("#qtype_drawing_editor_'.$question->id.'").set("height","100%");
                                               // Y.one("#qtype_drawing_editor_'.$question->id.'").setStyle("max-height","100px");
                                                document.getElementById("qtype_drawing_editor_'.$question->id.'").style.maxHeight = Y.one("body").get("winHeight")+"px";
                                                //Y.one("#qtype_drawing_stem_'.$question->id.'").setStyle("display", "block");

											}else{
												Y.one("#qtype_drawing_editor_"+'.$question->id.').setStyle("height", doc.get("winHeight") - 25 + "px");
											}
											if (document.getElementById("quiz-timer")) {
													var drawing_fullsc_'.$question->id.' = document.getElementById("quiz_timer_drawing_'.$question->id.'");
													drawing_fullsc_'.$question->id.'.appendChild(document.getElementById("quiz-timer").cloneNode(true));
													var quiz_timer_div = document.getElementById("quiz-time-left");
													if(quiz_timer_div && quiz_timer_div && quiz_timer_div.innerHTML === ""){
															Y.one("#quiz_timer_drawing_"+'.$question->id.').setStyle("display", "none");
															Y.one("#qtype_drawing_editor_"+'.$question->id.').setStyle("height","100%");
													} else {
															Y.one("#quiz_timer_drawing_"+'.$question->id.').setStyle("display", "block");
													}
											}
											 if (!Y.one("#qtype_drawing_drawingwrapper_"+'.$question->id.').hasClass("qtype_drawing_maximized") && lastHeight > 0) {
													 var viewportHeight_resized = doc.get("winHeight");
													 if(viewportHeight_resized && viewportHeight_resized > 600) viewportHeight_resized = 600;
																Y.one("#qtype_drawing_editor_'.$question->id.'").set("height", viewportHeight_resized);
																Y.one("#qtype_drawing_editor_'.$question->id.'").setStyle("height", viewportHeight_resized + "px");
																if (document.getElementById("quiz-timer")) {
																	var drawing_fullsc_'.$question->id.' = document.getElementById("quiz_timer_drawing_'.$question->id.'");
																	drawing_fullsc_'.$question->id.'.innerHTML = "";
																	Y.one("#quiz_timer_drawing_"+'.$question->id.').setStyle("display", "none");
																}
														}
														if(quiz_timer_div && quiz_timer_div.innerHTML !== "" && Y.one("#qtype_drawing_drawingwrapper_"+'.$question->id.').hasClass("qtype_drawing_maximized")){
															 var viewportHeight_resized = doc.get("winHeight");
															 var timer_height = document.getElementById("quiz_timer_drawing_'.$question->id.'").clientHeight;
															 viewportHeight_resized = viewportHeight_resized - timer_height;
															 Y.one("#qtype_drawing_editor_'.$question->id.'").set("height", viewportHeight_resized);
															 Y.one("#qtype_drawing_editor_'.$question->id.'").setStyle("height", viewportHeight_resized +10 + "px");
															 Y.one("#qtype_drawing_editor_'.$question->id.'").setStyle("top", "20px");
console.error("here");
														}
											});
									});
							//]]
							</script>

				<textarea style="display:none" id="qtype_drawing_background_image_value_'.$question->id.'">'.$background[1].'</textarea>
				<input type="hidden" style="display:none" id="qtype_drawing_background_image_type_'.$question->id.'" value="'.$background[0].'">
				<input type="hidden" style="display:none" id="qtype_drawing_background_image_width_'.$question->id.'" value="'.$canvasinfo->backgroundwidth.'">
				<input type="hidden" style="display:none" id="qtype_drawing_background_image_height_'.$question->id.'" value="'.$canvasinfo->backgroundheight.'">
				<div class="qtype_drawing_drawingwrapper" id="qtype_drawing_drawingwrapper_'.$question->id.'"><img id="qtype_drawing_loading_image_'.$question->id.'" src="'.$CFG->wwwroot.'/question/type/drawing/images/loading.gif" alt="Loading">
                <!--<span id="qtype_drawing_stem_' . $question->id .'" style="display:none; background-color:red"></span>-->
                <span id="quiz_timer_drawing_' . $question->id .'" style="display:none; margin-top:-1em; background-color:#fff"></span>
				<span class="qtype_drawing_togglebutton" id="qtype_drawing_togglebutton_id_' .$question->id . '">&nbsp;</span>
					<iframe src="'.$CFG->wwwroot.'/question/type/drawing/drawingarea.php?id='.$question->id.'&sesskey='.sesskey().'" id="qtype_drawing_editor_'.$question->id.'"  onload="init_qtype_drawing_embed('.$question->id.')" ></iframe>
				</div>
				';
			}

			$canvas .=  '</div>';

			$questiontext = $question->format_questiontext($qa);

			$result = html_writer::tag('div', $questiontext . $canvas, array('class' => 'qtext'));

			if ($qa->get_state() == question_state::$invalid) {
				$result .= html_writer::nonempty_tag('div',
						$question->get_validation_error(array('answer' => $currentAnswer)),
						array('class' => 'validationerror'));
			}
			return $result;
	}

	public function specific_feedback(question_attempt $qa) {
		$question = $qa->get_question();

		$answer = $question->get_matching_answer(array('answer' => $qa->get_last_qt_var('answer')));
		if (!$answer || !$answer->feedback) {
			return '';
		}

		return $question->format_text($answer->feedback, $answer->feedbackformat,
				$qa, 'question', 'answerfeedback', $answer->id);
	}

	public function correct_response(question_attempt $qa) {
		return ''; /* still not sure what kind of text should be given back for this....*/
		$question = $qa->get_question();

		$answer = $question->get_matching_answer($question->get_correct_response());
		if (!$answer) {
			return '';
		}

		return get_string('correctansweris', 'qtype_drawing', s($answer->answer));
	}




    public static function get_image_for_question($question) {
    	return self::get_image_for_files($question->contextid,  'qtype_drawing', 'qtype_drawing_image_file', $question->id);
    }

    public static function get_image_for_files($context, $component, $filearea, $itemid) {
    	$fs = get_file_storage();
    	$files = $fs->get_area_files($context,  $component, $filearea, $itemid, 'id');
    	if ($files) {
    		foreach ($files as $file) {
    			if ($file->is_directory()) {
    				continue;
    			}
    			if ($file->get_content() == null) {
    				return null;
    			}
					if($file->get_mimetype() == 'image/svg+xml') { // SVG.
						return array('svg', $file->get_content(), $file->get_filename());
					}
    			$image = imagecreatefromstring($file->get_content());
    			if ($image === false) {
    				return null;
    			}
    			$imgdatauri = self::gdimage_to_datauri($image);
    			imagedestroy($image);
    			return array('datauri',  $imgdatauri, $file->get_filename());
    		}
    	}
    	return null;
    }
    public static function isDataURLAValidDrawing($dataURL, $bgWidth, $bgHeight) {
		return true;
		/*
    	$imgData = base64_decode(qtype_drawing_renderer::strstr_after($dataURL, 'base64,'));
    	$imgGDResource =  imagecreatefromstring($imgData);
    	if ($imgGDResource === FALSE) {
    		return false;
    	} else {
    		// Check that it has non-zero dimensions (would've been nice to check that its dimensions fit those of the uploaded file but perhaps that is an overkill??)
    		if (imagesx($imgGDResource) != $bgWidth || imagesy($imgGDResource) != $bgHeight) {
    			return false;
    		} else {
    			// Check that the image is non-empty
    			if (self::isImageTransparent($imgGDResource, $bgWidth, $bgHeight) === true) {
    				return false;
    			}
    		}
    		imagedestroy($imgGDResource);
    		return true;
    	}
    	return false;
		*/
    }


    private static function isImageTransparent($gdImage, $width, $height) {
    	for ($x = 0; $x < $width; $x++) {
    		for ($y = 0; $y < $height; $y++) {
    			// Check the alpha channel (4th byte from the right) if it's completely transparent
    			if (((imagecolorat($gdImage, $x, $y) >> 24) & 0xFF) !== 127/*127 means completely transparent*/) {
    				// Something is painted, great!
    				return false;
    			}
    		}
    	}
    	return true;
    }


}

class drawing_blob_data_as_file_stream {

	private static $blob_data_position = 0;
	public static $blob_data_stream = '';

	public static function stream_open($path,$mode,$options,&$opened_path){
		static::$blob_data_position = 0;
		return true;
	}

	public static function stream_seek($seek_offset,$seek_whence){
		$blob_data_length = strlen(static::$blob_data_stream);
		switch ($seek_whence) {
			case SEEK_SET:
				$new_blob_data_position = $seek_offset;
				break;
			case SEEK_CUR:
				$new_blob_data_position = static::$blob_data_position+$seek_offset;
				break;
			case SEEK_END:
				$new_blob_data_position = $blob_data_length+$seek_offset;
				break;
			default:
				return false;
		}
		if (($new_blob_data_position >= 0) AND ($new_blob_data_position <= $blob_data_length)){
			static::$blob_data_position = $new_blob_data_position;
			return true;
		}else{
			return false;
		}
	}

	public static function stream_tell(){
		return static::$blob_data_position;
	}

	public static function stream_read($read_buffer_size){
		$read_data = substr(static::$blob_data_stream,static::$blob_data_position,$read_buffer_size);
		static::$blob_data_position += strlen($read_data);
		return $read_data;
	}

	public static function stream_write($write_data){
		$write_data_length=strlen($write_data);
		static::$blob_data_stream = substr(static::$blob_data_stream,0,static::$blob_data_position).
		$write_data.substr(static::$blob_data_stream,static::$blob_data_position+=$write_data_length);
		return $write_data_length;
	}

	public static function stream_eof(){
		return static::$blob_data_position >= strlen(static::$blob_data_stream);
	}

}
