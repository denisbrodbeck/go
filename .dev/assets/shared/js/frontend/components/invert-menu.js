export default function invertNavbarColor(headerId = "site-header", inverterClass = "twc-main-nav-inverted") {
	const header = document.getElementById(headerId);
	let isInverted = header.classList.contains(inverterClass);

	// use requestAnimationFrame as a built-in throttle function
	let ticking = false;
	function requestTick() {
	  if (!ticking) {
		requestAnimationFrame(function () {
		  const scrollpos = window.pageYOffset; // pageYOffset is supported on all browsers + IE11

		  if (scrollpos < 10 && !isInverted) {
			// we are at the top and menu is not yet inverted
			header.classList.add(inverterClass);
			isInverted = true;
		  }
		  if (scrollpos > 10 && isInverted) {
			// we are below the top and menu is inverted and needs to become regular
			header.classList.remove(inverterClass);
			isInverted = false;
		  }
		  ticking = false;
		});
		ticking = true;
	  }
	}

	const scrollOptions = { capture: false, passive: true };
	document.addEventListener("scroll", requestTick, scrollOptions);
  }
