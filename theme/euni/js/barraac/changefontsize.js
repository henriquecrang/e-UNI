var min=8;
var max=16;

	function increaseFontSize() {
	   var p = document.getElementsByTagName('div'); //pega todos os elementos com a tag div
	

	   for(i=7;i<p.length;i++) {

// solução bacalhau que pula todos os divs que existem antes da barra de acessibilidade
// divs atuais pulados: 'surround''page''mec''mecConteudo''acessibilidade''ace_conteudo''direita'





	      if(p[i].style.fontSize) {
		 var s = parseInt(p[i].style.fontSize.replace("px",""));
	      } else {
		 var s = 12;
	      }
	      if(s!=max) {
		 s += 1;
	      }
	      p[i].style.fontSize = s+"px"
	   }//fecha for
	}//fecha função





	function decreaseFontSize() {
	   var p = document.getElementsByTagName('div'); //pega todos os elementos com a tag div
	   for(i=7;i<p.length;i++) {

// solução bacalhau que pula todos os divs que existem antes da barra de acessibilidade
// divs atuais pulados: 'surround''page''mec''mecConteudo''acessibilidade''ace_conteudo''direita'

	      if(p[i].style.fontSize) {
		 var s = parseInt(p[i].style.fontSize.replace("px",""));
	      } else {
		 var s = 12;
	      }
	      if(s!=min) {
		 s -= 1;
	      }
	      p[i].style.fontSize = s+"px"
	   } //fecha for  
	} //fecha função

