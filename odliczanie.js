var zmienna= new Date();
var dataWydarzenia = zmienna.getTime() + 7200000;
function odliczanie()
{
var pozostalo=document.getElementById("pozostalo");
var aktualnyCzas = new Date();
var pozostalyCzas = dataWydarzenia - aktualnyCzas.getTime();

if (pozostalyCzas > 0)
    {                        
        var s = pozostalyCzas / 1000;
        var min = s / 60;            
        var h = min / 60;            
        var d = h / 24;              

        var poz_s = Math.floor(s  % 60);
        var poz_min = Math.floor(min % 60);
        var poz_godz = Math.floor(h);      
        
        if (poz_min < 10)						
          poz_min = "0" + poz_min;
        if (poz_s < 10)							
          poz_s = "0" + poz_s;
        if (poz_godz < 10)					
          poz_godz = "0" + poz_godz;
        pozostalo.value=(poz_godz + ':' + poz_min + ':' + poz_s);
		  
		setTimeout("odliczanie()",1000);
    }
		else			
		{
		pozostalo.value=("Koniec odliczania");
		clearTimeout;
		}
}