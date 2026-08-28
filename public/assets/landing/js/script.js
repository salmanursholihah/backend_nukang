document.addEventListener('DOMContentLoaded', () => {
  
  // 1. Navbar Shadow & Sticky
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 20) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  });

  // 2. Hamburger Menu Toggle (Mobile)
  const hamburger = document.getElementById('hamburger');
  const navMenu = document.getElementById('nav-menu');
  const navLinks = document.querySelectorAll('.nav-links a');

  if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('active');
      navMenu.classList.toggle('active');
    });

    // Close menu when clicking a link
    navLinks.forEach(link => {
      link.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
      });
    });
  }

  // 3. Scroll Reveal using IntersectionObserver
  const revealElements = document.querySelectorAll('.reveal');
  const revealObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('active');
        observer.unobserve(entry.target);
      }
    });
  }, { 
    threshold: 0.15,
    rootMargin: "0px 0px -50px 0px"
  });

  revealElements.forEach(el => revealObserver.observe(el));

  // 4. Promo Slider
  const slides = document.querySelectorAll('.slide');
  const dots = document.querySelectorAll('.dot');
  let currentSlide = 0;
  let slideInterval;

  if (slides.length > 0) {
    function showSlide(index) {
      slides.forEach(s => s.classList.remove('active'));
      dots.forEach(d => d.classList.remove('active'));
      
      slides[index].classList.add('active');
      dots[index].classList.add('active');
      currentSlide = index;
    }

    function nextSlide() {
      let next = (currentSlide + 1) % slides.length;
      showSlide(next);
    }

    function startSlider() {
      slideInterval = setInterval(nextSlide, 3500);
    }

    function resetSlider() {
      clearInterval(slideInterval);
      startSlider();
    }

    dots.forEach(dot => {
      dot.addEventListener('click', (e) => {
        const index = parseInt(e.target.getAttribute('data-index'));
        showSlide(index);
        resetSlider();
      });
    });

    startSlider();
  }

  // 5. Stat Counters Animation
  const statItems = document.querySelectorAll('.stat-number');
  const statsObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const targetEl = entry.target;
        const targetValue = parseFloat(targetEl.getAttribute('data-target'));
        const isDecimal = targetEl.getAttribute('data-is-decimal') === 'true';
        animateValue(targetEl, 0, targetValue, 2000, isDecimal);
        observer.unobserve(targetEl);
      }
    });
  }, { threshold: 0.5 });

  statItems.forEach(item => statsObserver.observe(item));

  function animateValue(obj, start, end, duration, isDecimal) {
    let startTimestamp = null;
    const step = (timestamp) => {
      if (!startTimestamp) startTimestamp = timestamp;
      const progress = Math.min((timestamp - startTimestamp) / duration, 1);
      
      // Easing function for smooth deceleration
      const easeOutQuart = 1 - Math.pow(1 - progress, 4);
      let currentVal = easeOutQuart * (end - start) + start;
      
      if (isDecimal) {
        obj.innerHTML = currentVal.toFixed(1);
      } else {
        // Format with dot separator for thousands
        obj.innerHTML = Math.floor(currentVal).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        if (progress === 1 && end >= 1000) {
            obj.innerHTML += "+";
        }
      }
      
      if (progress < 1) {
        window.requestAnimationFrame(step);
      }
    };
    window.requestAnimationFrame(step);
  }

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href');
      if (targetId === '#') return;
      
      const targetEl = document.querySelector(targetId);
      if (targetEl) {
        e.preventDefault();
        
        // Account for fixed navbar
        const headerOffset = 80;
        const elementPosition = targetEl.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
  
        window.scrollTo({
          top: offsetPosition,
          behavior: "smooth"
        });
      }
    });
  });
});