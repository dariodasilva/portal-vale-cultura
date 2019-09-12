				AnimsDemo = function(io){
				addOnLoad = function(){
					io.addObj(this[0],this[1],this[2])
				};

				//yeti scooter
				var spaceGuySrcs = [
							'img/character/walk/walk-1.png'
						   ,'img/character/walk/walk-2.png'
						   ,'img/character/walk/walk-3.png'
						   ,'img/character/walk/walk-4.png'
						   ,'img/character/walk/walk-5.png'
						   ,'img/character/walk/walk-6.png'
						   ,'img/character/walk/walk-7.png'
						   ,'img/character/walk/walk-8.png'
						   ,'img/character/walk/walk-9.png'
						   ,'img/character/walk/walk-10.png'
						   ,'img/character/walk/walk-11.png'
						   ,'img/character/walk/walk-12.png'
						   ,'img/character/walk/walk-13.png'
						   ,'img/character/walk/walk-14.png'
						   ,'img/character/walk/walk-15.png'];

				var spaceGuy = new iio.Rect(180, io.canvas.height-100);
				spaceGuy.createWithAnim(spaceGuySrcs, addOnLoad.bind([spaceGuy,0,0]));
				io.setFramerate(10, function(){
					spaceGuy.nextAnimFrame();
				});
			}; iio.start(AnimsDemo, 'c1');