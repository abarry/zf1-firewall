package  {
	
	import flash.display.MovieClip;
	import flash.utils.*;
	
	public class Disque extends MovieClip {
		
		private var posX:uint = NaN;
		private var posY:uint = NaN;
		static private var registryDisques:Array = new Array(new Array(), new Array(), new Array());
		static private var positions:Array = new Array(105,275,437);
		
		public function Disque(positionX) {
			// constructor code
			Disque.registryDisques[positionX].push(this);
			var positionInStack = Disque.registryDisques[positionX].indexOf(this);
			this.scaleY = this.scaleX = 1-positionInStack*0.1;
			this.posX = positionX;
			this.posY = positionInStack;
			this.y = 400 - this.height/2 - this.posY * 6;
			this.x = Disque.positions[positionX];
		}
		
		public function goTo(positionX,callBack=null):void
		{
			if (callBack == null) callBack = function(){};
			registryDisques[this.posX].splice(registryDisques[this.posX].indexOf(this),1);
			this.posX = positionX;
			Disque.registryDisques[positionX].push(this);
			var positionInStack = Disque.registryDisques[positionX].indexOf(this);
			this.posY = positionInStack;
			this.moveTo(this.x,200,
						function (moi) {
							return function() { 
								moi.moveTo(Disque.positions[positionX],200,
									function() {
										moi.moveTo(Disque.positions[positionX],400 - moi.height/2 - moi.posY * 6,callBack);
									})
							}
						} (this)
						);

		}
		
		private function moveTo(xx,yy, callBack=null)
		{
			if (callBack == null) callBack = function() { };
			if (this.x < xx) this.x += 20;
			if (this.x > xx) this.x -= 20;
			if (this.y < yy) this.y += 20;
			if (this.y > yy) this.y -= 20;
			
			if (this.x > xx-20 && this.x < xx+20) this.x = xx;
			if (this.y > yy-20 && this.y < yy+20) this.y = yy;
			
			if (this.x == xx && this.y == yy) callBack();
			else setTimeout(this.moveTo,5,xx,yy,callBack);

			
		}
	
	}
	
}
