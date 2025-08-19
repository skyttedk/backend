<?php

Class gavealiasController Extends baseController {

    public function Index()
    {
        echo "NO ACCESS!";
    }

    private function dialogError($errorMessage)
    {
        echo "<div style='padding: 20px; text-align: center; color: red; font-size: 1.2em;'>".$errorMessage."</div>";
    }
    
    private function loadShopPresentStatus($shopID)
    {
       // Get orders
        $shoporders = Order::find_by_sql("SELECT present_id FROM `order` WHERE shop_id = ".intval($shopID));
        $activePresents = array();
        if(count($shoporders) > 0)
        {
          foreach($shoporders as $order)
          {
            $activePresents[] = $order->present_id;
          }
        }
        
        // Get shoppresent
        $shoppresentList = ShopPresent::find('all',array("conditions" => array('shop_id = ?',$shopID)));                           
        $shoppresentStatus = array();
        
        foreach($shoppresentList as $sp)
        {
          $hasOrders = in_array($sp->present_id,$activePresents);
          if($sp->active == 1 && $sp->is_deleted == 0)
          {
            $shoppresentStatus[$sp->present_id] = array("active" => true, "text" => "", "index" => $sp->index_);
          }
          else if($hasOrders)
          {
              if($sp->is_deleted == 1) 
              {
                $shoppresentStatus[$sp->present_id] = array("active" => true, "text" => "Slettet men har ordre tilknyttet", "index" => $sp->index_);               
              }
              else if($sp->active == 0)
              {
                  $shoppresentStatus[$sp->present_id] = array("active" => true, "text" => "Deaktiveret men har ordre tilknyttet", "index" => $sp->index_);
              }
          }
          else if($sp->is_deleted == 1) 
          {
            $shoppresentStatus[$sp->present_id] = array("active" => false, "text" => "Slettet", "index" => $sp->index_);               
          }
          else if($sp->active == 0)
          {
              $shoppresentStatus[$sp->present_id] = array("active" => false, "text" => "Deaktiveret", "index" => $sp->index_);
          }
          
        }
        return $shoppresentStatus;
    }

    public function dialog()
    {
        
        
        // Get and check shop
        $shopID = isset($_POST["shopid"]) ? intval($_POST["shopid"]) : 0;
        if($shopID == 0)
        {
            $this->dialogError("Kunne ikke finde shop");
            exit();
        }

        // Get shop presents
        /*
        $shopPresents = ShopPresent::find('all',array("conditions" => array('shop_id = ?',$shopID),"order" => "index_ asc"));                                                                                                                             
        if(count($shopPresents) == 0)
        {
            $this->dialogError("Der er ikke nogen gaver tilknyttet shoppen.");
            exit();
        }
        */
        // Get presents
        $presentList = Present::find('all',array("conditions" => array('shop_id = ?',$shopID),"order" => "alias asc"));                                                                                                                             
        if(count($presentList) == 0)
        {
            $this->dialogError("Der er ikke nogen gaver tilknyttet shoppen.");
            exit();
        }
        
        $shoppresentStatus = $this->loadShopPresentStatus($shopID);    
        
        // Add presents with alias in one list, those without in another
        $hasAliasList = array();
        $noAliasList = array();
        
        foreach($presentList as $present)
        {
          if($present->alias == "" || intval($present->alias) == 0)
          {
            $noAliasList[] = $present;
          }
          else $hasAliasList[] = $present;
        }  
        
        // Order no alias list by index
        if(count($noAliasList) > 0)
        {
       
          for($i=0;$i<count($noAliasList);$i++)
          {
            for($j=$i+1;$j<count($noAliasList);$j++)
            {
              $indexi = isset($shoppresentStatus[$noAliasList[$i]->id]) ? $shoppresentStatus[$noAliasList[$i]->id]["index"] : 0;
              $indexj = isset($shoppresentStatus[$noAliasList[$j]->id]) ? $shoppresentStatus[$noAliasList[$j]->id]["index"] : 0;
            //  if(($indexi == $indexj)) echo "SIM ".$noAliasList[$i]->id." / ".$noAliasList[$j]->id;
              if($indexi > $indexj || ($indexi == $indexj && $noAliasList[$i]->id > $noAliasList[$j]->id))
              {                          
                $tmp = $noAliasList[$i];
                $noAliasList[$i] = $noAliasList[$j];
                $noAliasList[$j] = $tmp;
              }                 
            }
          }
        }
        
        // Merge lists, no alias at top
        $presentList = array_merge($noAliasList,$hasAliasList);
        
        // Start dialog
        ?><div style="padding: 10px;">
            Angiv et alias-navn til de valgte gaver i shoppen, samt en rækkefølge for sortering.
        </div>

        <table style="width: 100%;">
            <thead>
                <th style="text-align: left; border-bottom: 1px solid black;">Nr</th>
                <th style="text-align: left; border-bottom: 1px solid black;">Gave</th>
            </thead>
            <tbody id="gavealiaslist">
                  <div style="display: none">
            <pre>
            <?php print_r($presentList); ?>
            <?php print_r($shoppresentStatus); ?>
            </pre>
        </div>
            <?php
            
            foreach($presentList as $present)
            {
            
                $status = isset($shoppresentStatus[$present->id]) ? $shoppresentStatus[$present->id] : array("active" => true, "text" => "Kunne ikke finde shopgave ".$present->id);
             
                if($status["active"] == true)
                {
                //$presentModels = PresentModel::find('all',array("conditions" => array('present_id = ?',$present->id),"order" => "aliasletter asc"));            
                $presentModels = PresentModel::find_by_sql("SELECT model_id, model_name, model_no, aliasletter, fullalias FROM `present_model` WHERE `present_id` = ".intval($present->id)." && (active = 0 or (active = 1 and model_id IN (SELECT present_model_id FROM `order` WHERE present_model_id > 0))) && language_id = 1 GROUP BY model_id ORDER BY aliasletter ASC, language_id ASC");
            
                ?><tr class="aliasrow">
                    <td style="padding: 5px;" valign=top>
			                 <input type="text" size="10" class="aliasnumber" name="shop_present_<?php echo $present->id; ?>" value="<?php echo ($present->alias > 0 ? $present->alias : ""); ?>">
		                </td>
                    <td style="padding: 5px;">
                      <?php echo $present->nav_name; if($status["text"] != "") echo "<i>(".$status["text"].")</i>"; ?>
                  
                      <?php if(count($presentModels) > 0) { ?>
                        <div class="modellist" style="padding-left: 30px; margin-bottom: 10px; <?php if(count($presentModels) == 1) echo "display: none;"; ?>">
                        <?php foreach($presentModels as $model) { ?>
                          <div style="clear: both; padding-top: 5px; text-align: right;">
                            <?php echo $model->model_name.", ".$model->model_no; ?> 
                            <input type=text class="modelaliastext" maxlength="1" name="modelalias_<?php echo $model->model_id ?>" value="<?php echo (count($presentModels) > 1 && $model->aliasletter != "") ? $model->aliasletter : ""; ?>" size=3>
                          </div>
                        <?php } ?>
                      </div>
                    <?php } ?>  
                    </td>
                </tr><?php
                }
                
            }
            

            ?></tbody>
        </table><?php
                  
    }

	public function save()
	{

    // Find / check shop
	 	$shopID = isset($_POST["shopid"]) ? intval($_POST["shopid"]) : 0;
    if($shopID == 0)
    {
	    echo json_encode(array("status" => 0,"error" => "Kunne ikke finde shop"));
		  return;
    }

    // Check gavealias
		if(count($_POST["gavealias"]) == 0)
		{
			echo json_encode(array("status" => 0,"error" => "Ingen gavealias angivet"));
		  return;
		}

    $presentList = Present::find('all',array("conditions" => array('shop_id = ?',$shopID),"order" => "alias asc"));                          
    if(count($presentList) == 0)
		{
			echo json_encode(array("status" => 0,"error" => "Ingen gaver tilknyttet shop"));
		    	return;
		}

    $shoppresentStatus = $this->loadShopPresentStatus($shopID);        
      

		$usedAliases = array();
    
		foreach($presentList as $present)
    {		
       $status = isset($shoppresentStatus[$present->id]) ? $shoppresentStatus[$present->id] : array("active" => true, "text" => "Kunne ikke finde shopgave ".$present->id);
             
      if($status["active"] == true)
      {
    
  			if(!isset($_POST["gavealias"]["shop_present_".$present->id]) || intval($_POST["gavealias"]["shop_present_".$present->id]) <= 0)
  			{
  				echo json_encode(array("status" => 0,"error" => "Angiv alias til gaven: ".$present->nav_name." (".$present->id.")"));
  				return;
  			}
  
  			$alias = intval($_POST["gavealias"]["shop_present_".$present->id]);
  			if(in_array($alias,$usedAliases))
  			{
  				echo json_encode(array("status" => 0,"error" => $present->nav_name.": alias '".$alias." allerede brugt.'"));
  				return;
  			}
        
        // Update present
        $usedAliases[] = $alias;
        $present->alias = $alias;
        $present->save();
        
        // Update models      
        $modelList = PresentModel::all(array("present_id" => $present->id)); 
        $usedAliasLetters = array();
        
        $activeModels = PresentModel::find_by_sql("SELECT model_id, model_name, model_no, aliasletter, fullalias FROM `present_model` WHERE `present_id` = ".intval($present->id)." && (active = 0 or model_id IN (SELECT present_model_id FROM `order`)) && language_id = 1 GROUP BY model_id ORDER BY aliasletter ASC, language_id ASC");
        $activeMap = array();
        foreach($activeModels as $model) $activeMap[] = $model->model_id;
        
        // Make map with model_id
        $modelMap = array();
        foreach($modelList as $model)
        {
          if(in_array($model->model_id,$activeMap))
          {
            $modelMap[$model->model_id][] = $model;
          }
        }
        
        
        // Process each model_id
        foreach($modelMap as $model_id => $models)
        {
        
          if(count($modelMap) > 1 && (!isset($_POST["modelalias"]["modelalias_".$model_id]) || trimgf($_POST["modelalias"]["modelalias_".$model_id]) == "" || intval(trimgf($_POST["modelalias"]["modelalias_".$model_id])) > 0))
          {
            echo json_encode(array("status" => 0,"error" => "Angiv et bogstav alias til ".$present->nav_name." - ".$models[0]->model_no));
    				return;
          }
          else
          {
              if(count($modelMap) == 1) $aliasletter = "";
              else $aliasletter = strtolower($_POST["modelalias"]["modelalias_".$model_id]);
              
              if(in_array($aliasletter,$usedAliasLetters))
        			{
        				echo json_encode(array("status" => 0,"error" => "Alias bogstav ".$aliasletter." til ".$present->nav_name." - ".$models[0]->model_no." benyttes allerede"));
        				return;
        			}
              else
              {
                $usedAliasLetters[] = $aliasletter;
                foreach($models as $model)
                {
                  $model->aliasletter = $aliasletter;
                  $model->fullalias = $alias.$aliasletter;
                  $model->save();
                }
              }
          }
          
        }
      }

		}

		// Check for doubles
        $presentDoubles = Present::find_by_sql("SELECT alias, count(id) FROM present WHERE shop_id = ".intval($shopID)." && alias > 0 GROUP BY alias HAVING COUNT(id) > 1");
		if(count($presentDoubles) > 0) {

		    $aliasList = array();
		    foreach($presentDoubles as $pd) $aliasList[] = $pd->alias;
            echo json_encode(array("status" => 0,"error" => "Følgende alias er i shoppen flere gange: ".implode(",",$aliasList).", evt. er en gave med det alias slettet og det kan ikke bruges igen."));
            return;

        }

        $modelDoubles = PresentModel::find_by_sql("SELECT fullalias, count(id) FROM present_model WHERE present_id in (select id from present where shop_id = ".intval($shopID).") && fullalias != '' && language_id = 1 GROUP BY fullalias HAVING COUNT(id) > 1");
        if(count($modelDoubles) > 0) {

            $aliasList = array();
            foreach($modelDoubles as $pd) $aliasList[] = $pd->alias;
            echo json_encode(array("status" => 0,"error" => "Følgende alias er i shoppen flere gange: ".implode(",",$aliasList).", evt. er en gave med det alias slettet og det kan ikke bruges igen."));
            return;

        }
        
		response::success(json_encode(array()));
	}

}
