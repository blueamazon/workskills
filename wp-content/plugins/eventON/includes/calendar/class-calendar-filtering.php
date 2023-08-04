<?php
/**
* Calendar Filtering
*/

class EVO_Cal_Filering{
	public function __construct(){
		$this->cal = EVO()->evo_generator;
		add_filter('evo_cal_above_header_btn', array($this, 'cal_header_btn'),10,2);
		add_action('evo_cal_above_header_btns_end', array($this,'sort_icon'), 10,1);
	}

	function cal_header_btn( $A, $arg){
		$sortfilter = true;

		if( $this->cal->evcal_hide_sort == 'yes') return $A;
		if(isset($arg['hide_so']) && $arg['hide_so'] == 'yes') return $A;
		$A['evo-filter-btn'] = '';
		return $A;
	}

	function sort_icon($args){
		if( $this->cal->evcal_hide_sort == 'yes') return false;
		if(isset($args['hide_so']) && $args['hide_so'] == 'yes') return false;

		$sorting_options = (!empty($this->cal->evopt1['evcal_sort_options']))?$this->cal->evopt1['evcal_sort_options']:array();

		if( count ($sorting_options) <1) return false;
		
		echo "<span class='evo-sort-btn'>";
		$this->get_sort_content($args);
		echo "</span>";
	}

	// for header buttons
	function get_sort_content($args){
		

		if( $this->cal->evcal_hide_sort != 'yes'){ // if sort bar is set to show

			$sorting_options = (!empty($this->cal->evopt1['evcal_sort_options']))?$this->cal->evopt1['evcal_sort_options']:array();

			// sorting section
			$evsa1 = array(
				'date'=>'Date',
				'title'=>'Title',
				'color'=>'Color',
				'posted'=>'Post Date'
			);
			$sort_options = array(	1=>'sort_date', 'sort_title','sort_color','sort_posted');
				$__sort_key = substr($args['sort_by'], 5);

			if(count($sorting_options)>0){
				echo "<div class='eventon_sort_line' style='display:none'>";

					$cnt =1;
					foreach($evsa1 as $so=>$sov){
						if(in_array($so, $sorting_options) || $so=='date' ){
						echo "<p data-val='sort_".$so."' data-type='".$so."' class='evs_btn ".( ($args['sort_by'] == $sort_options[$cnt])? 'evs_hide':null)."' >"
								.$this->cal->lang('evcal_lang_s'.$so,$sov)
								."</p>";
						}
						$cnt++;
					}
				echo "</div>";
			}
		}

	}

	// Calendar header filter and sort content
		public function get_content($args, $sortbar=true){

			//if(!$sortbar) return false;

			//if( $this->cal->evcal_hide_sort == 'yes') return false;

			// define variable values	
			$filtering_options = (!empty($this->cal->evopt1['evcal_filter_options']))?$this->cal->evopt1['evcal_filter_options']:array();
			$content='';

			$this->cal->reused(); // update reusable variables real quikc

			ob_start();

			$SO_display = (!empty($args['exp_so']) && $args['exp_so'] =='yes')? 'block': 'none';

			echo "<div class='eventon_sorting_section' style='display:{$SO_display}'>";

			$__text_all_ = $this->cal->lang('evcal_lang_all', 'All');
			
			// EACH EVENT TYPE
				$_filter_array = array();
				$_filter_array['evpf']= 'event_past_future';
				$__event_types = $this->cal->shell->get_event_types();
				foreach($__event_types as $ety=>$event_type){
					$_filter_array[$ety]= $event_type;
				}
				$_filter_array['evloc']= 'event_location';
				$_filter_array['evorg']= 'event_organizer';
				$_filter_array['evotag']= 'event_tag';
			
			// hook for additional taxonomy filters
				$_filter_array = apply_filters('eventon_so_filters', $_filter_array);


			// Filtering TYPE  /select / default
			$selectfilterType = (!empty($args['filter_type']) && $args['filter_type']=='select')? true: false;

			echo "<div class='eventon_filter_line ".($selectfilterType?'selecttype':'')."'>";
				//print_r($_filter_array);
					
				// For each taxonomy
				foreach($_filter_array as $ff=>$vv){ // vv = vv etc.

					// past and future filtering
						if($ff == 'evpf'){
							if(in_array($vv, $filtering_options)){
								$filter_type_name = evo_lang('Past & Future Events');
								echo "<div class='eventon_filter evo_hideshow_pastfuture' data-filter_field='{$vv}' data-filter_val='all' data-filter_type='custom' >								
									<div class='eventon_filter_selection'>
										<p class='filtering_set_val' data-opts='evs4_in'>{$filter_type_name}</p>
										<div class='eventon_filter_dropdown evo_hideshow select_one' style='display:none'>";

										echo "<p class='select all' data-filter_val='all'>{$__text_all_}</p>";
										echo "<p class='past ". ( $selectfilterType? 'select':'')."' data-filter_val='past'>". evo_lang('Only Past Events') ."</p>";
										echo "<p class='future ". ( $selectfilterType? 'select':'')."' data-filter_val='future'>". evo_lang('Only Future Events') ."</p>";
									echo "</div>
									</div><div class='clear'></div>
								</div>";
							}
							continue;
						}

					// Event Tags filtering
						if($ff == 'evotag' && in_array($vv, $filtering_options)){
							$tags = get_terms(apply_filters('evo_get_frontend_filter_tags',
								array( 
									'taxonomy'=>'post_tag',
									'hide_empty'=> true,
									'parent'=>0
								)
							));

							if(count($tags)>0):


							$__filter_val = (!empty($args[$vv])? $args[$vv]: 'all');
							$filtering_values = $__filter_val == 'all'? array(): explode(',', $__filter_val);

							echo "<div class='eventon_filter evo_hideshow_evotag' data-filter_field='post_tag' data-filter_val='all' data-filter_type='tax' >								
								
								<div class='eventon_filter_selection'>
									<p class='filtering_set_val' data-opts='evs4_in'>". evo_lang('Event Tag'). "</p>
									<div class='eventon_filter_dropdown evo_hideshow' style='display:none'>";

									echo "<p class='". ($__filter_val == 'all'? 'select':'')." all' data-filter_val='all'>{$__text_all_}</p>";

									// all event tags
									foreach($tags as $tag){
										echo "<p class='". ($__filter_val == 'all'? 'select':'')."' data-filter_val='{$tag->term_id}'>". $tag->name ."</p>";
									}
									
								echo "</div>
								</div><div class='clear'></div>
							</div>";

							endif;

							continue;
						}

					// hook for other arguments
					$cats = get_terms( apply_filters('evo_get_frontend_filter_tax',
						array( 
							'taxonomy'=> $vv,
							'hide_empty'=> false,
						)
					));
			
					// filtering value filter is set to show
					if(in_array($vv, $filtering_options) && $cats && strpos($args[$vv], 'NOT-')=== false){

						
						$inside ='';

						$__filter_val = (!empty($args[$vv])? $args[$vv]: 'all');
						$filtering_values = $__filter_val == 'all'? array(): explode(',', $__filter_val);

						// INSIDE drop down
						$inside .=  "<p class='". ($__filter_val == 'all'? 'select':'')." all' data-filter_val='all'>{$__text_all_}</p>";

						// each taxonomy term
						foreach($cats as $ct){

							//print_r($filtering_values);
							// skip shortcode via set filter values from showing in list
								//if(in_array($ct->term_id, $filtering_values) && !$selectfilterType) continue;
							
							$select = ( in_array($ct->term_id, $filtering_values)  || $__filter_val == 'all') ? 'select':'';

							// if term is parent level
							$par = $ct->parent == 0? true:false;
							
							$term_name = $this->cal->lang('evolang_'.$vv.'_'.$ct->term_id,$ct->name );
							if(!$selectfilterType){
								// event type 1 tax icon
								$icon_str = $this->cal->helper->get_tax_icon($vv,$ct->term_id, $this->cal->evopt1 );

								$inside .=  "<p class='{$select} ".$ct->term_id.' '.$ct->slug.' '. ($icon_str?'has_icon':''). ($par?'':' np'). "' data-filter_val='".$ct->term_id."' data-filter_slug='".$ct->slug."'>". $icon_str . $term_name."</p>";
							}else{// checkbox select option
								
								$inside .=  "<p class='{$ct->term_id} {$select}' data-filter_val='".$ct->term_id."'>". $term_name."</p>";
							}
						}

						// only for event type taxonomies
						$_isthis_ett = (in_array($vv, $__event_types))? true:false;

						$ett_count = ($ff==1)? '':$ff;

						// Language for the taxonomy name text
						$lang__ = ($_isthis_ett)? 
							$this->cal->lang_array['et'.$ett_count]:
							(!empty($this->cal->lang_array[$ff])? $this->cal->lang_array[$ff]: 
								evo_lang(str_replace('_', ' ', $vv)) );

						// filter in or not
						$filter_op = 'IN';
						if(strpos($__filter_val, 'NOT-')!== false){

							$filter_op = 'NOT';
							$__filter_val = str_replace('NOT-', '', $__filter_val);
							//$__filter_val = substr($__filter_val, 4);
						}

						// update clickable text
						$__text_all = $selectfilterType? $lang__: $__text_all_;


						echo "<div class='eventon_filter evo_sortOpt evo_sortList_{$vv}' data-filter_field='{$vv}' data-filter_val='{$__filter_val}' data-filter_type='tax' data-fl_o='{$filter_op}'>
								<p class='filtering_set_val'>".$lang__."</p>
								<div class='eventon_filter_dropdown' style='display:none'>".$inside."</div>	
							<div class='clear'></div>
						</div>";
					}else{
						// if no tax values is passed
						if(!empty($args[$vv])){
							$taxFL = eventon_tax_filter_pro($args[$vv]);

							echo "<div class='eventon_filter' data-filter_field='{$vv}' data-filter_val='{$taxFL[0]}' data-filter_type='tax' data-fl_o='{$taxFL[1]}'></div>";
						}
					}
				}

				// for select filter type
				if($selectfilterType){
					echo "<p class='evo_filter_submit'>". $this->cal->lang('evcal_lang_apply_filters','Apply Filters')."</p>";
				}

				// (---) Hook for addon
				echo  do_action('eventon_sorting_filters', $content);

			echo "</div>"; // #eventon_filter_line


			echo "<div class='clear'></div>"; // clear

			echo "</div>"; // #eventon_sorting_section

			return ob_get_clean();
		}
}