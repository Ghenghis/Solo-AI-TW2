<?php namespace Twlan; ?>
$(document).ready(function(){
   $(document).mousemove(function(e){
      window.mouseXPos = e.pageX;
      window.mouseYPos = e.pageY;
   }); 
});

function knight_item_kill() {
	document.getElementById("tooltip").style.visibility = "hidden";
}

function knight_item_popup(name, des) {
	knight_item_move();
	obj = document.getElementById("tooltip");
    $(obj).toggle();
	obj.innerHTML = '<h3>'+name+'</h3><div class="body">'+des+'</div>';
	obj.style.visibility = "visible";
}

function knight_item_move() {
	var info = document.getElementById("tooltip");
	mx = window.mouseXPos;
    my = window.mouseYPos;
	info.style.left = (mx + 20) + "px";
	info.style.top = (my + 15) + "px";
}