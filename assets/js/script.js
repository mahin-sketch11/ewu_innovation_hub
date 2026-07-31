// Navbar shadow on scroll

window.addEventListener("scroll",function(){

    let navbar=document.querySelector(".navbar");


    if(window.scrollY>50){

        navbar.style.background="rgba(2,0,36,.95)";

    }

    else{

        navbar.style.background="rgba(11,19,43,.75)";

    }

});




// Scroll Animation

const cards=document.querySelectorAll(".feature-card");


window.addEventListener("scroll",()=>{


cards.forEach(card=>{


let position=card.getBoundingClientRect().top;


let screenHeight=window.innerHeight;


if(position < screenHeight-100){

    card.classList.add("show");

}


});


});