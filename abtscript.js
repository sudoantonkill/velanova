// scripts.js

document.addEventListener('DOMContentLoaded', () => {
    const faders = document.querySelectorAll('.fade-in-section');

    const options = {
        threshold: 0.1, // Trigger when 10% of the element is visible
        rootMargin: "0px 0px -50px 0px" // Adjust as needed
    };

    const appearOnScroll = new IntersectionObserver(function(entries, observer) {
        entries.forEach(entry => {
            if(entry.isIntersecting){
                entry.target.classList.add('visible');
                observer.unobserve(entry.target); // Stop observing once visible
            }
        });
    }, options);

    faders.forEach(fader => {
        appearOnScroll.observe(fader);
    });
});
