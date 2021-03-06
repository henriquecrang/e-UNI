<?php
class block_euni_sobre extends block_base {
  function init() {
    $this->title   = get_string('euni_sobre', 'block_euni_sobre');
    $this->version = 2010022301;
  }
  // The PHP tag and the curly bracket for the class definition 
  // will only be closed after there is another function added in the next section.

function get_content() {
    if ($this->content !== NULL) {
      return $this->content;
    }
 
    $this->content         =  new stdClass;
    $this->content->text   = $this->config->text;
    $this->content->footer = '';
 
    return $this->content;
  }
}   // Here's the closing curly bracket for the class definition
    // and here's the closing PHP tag from the section above.


function instance_allow_config() {
  return true;
}	// permite que editem o conteúdo

function specialization() {
  if(!empty($this->config->title)){
    $this->title = $this->config->title;
  }else{
    $this->config->title = 'Sobre';
  }
  if(empty($this->config->text)){
    $this->config->text = 'Aqui fica o sobre';
  }    
}

function instance_allow_multiple() {
  return true;
}

function has_config() {
  return true;
}


?>


