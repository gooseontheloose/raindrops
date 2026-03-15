// Raindrops Animation & Sleek Interactions

const canvas = document.getElementById("canvas-club");
const ctx = canvas.getContext("2d");
let w = canvas.width = window.innerWidth;
let h = canvas.height = window.innerHeight;
const drops = [];
const maxDrops = 100;
const clearColor = 'rgba(0, 0, 0, 0.15)'; // Slightly more trail for atmosphere

function random(min, max) {
    return Math.random() * (max - min) + min;
}

class RainDrop {
    constructor() {
        this.init();
    }

    init() {
        this.x = random(0, w);
        this.y = random(-h, 0);
        this.color = 'rgba(0, 242, 255, 0.45)';
        this.w = 1.5;
        this.h = 1;
        this.vy = random(6, 12); // Slightly slower for a more 'friendly' vibe
        this.vw = 3;
        this.vh = 1;
        this.size = random(1, 2.5);
        this.hit = random(h * 0.7, h);
        this.a = 1;
        this.va = 0.94;
    }

    draw() {
        if (this.y > this.hit) {
            ctx.beginPath();
            ctx.moveTo(this.x, this.y - this.h / 2);
            ctx.bezierCurveTo(
                this.x + this.w / 2, this.y - this.h / 2,
                this.x + this.w / 2, this.y + this.h / 2,
                this.x, this.y + this.h / 2
            );
            ctx.bezierCurveTo(
                this.x - this.w / 2, this.y + this.h / 2,
                this.x - this.w / 2, this.y - this.h / 2,
                this.x, this.y - this.h / 2
            );
            ctx.strokeStyle = `rgba(0, 242, 255, ${this.a * 0.25})`;
            ctx.stroke();
            ctx.closePath();
        } else {
            ctx.fillStyle = this.color;
            ctx.fillRect(this.x, this.y, this.size, this.size * 5);
        }
        this.update();
    }

    update() {
        if (this.y < this.hit) {
            this.y += this.vy;
        } else {
            if (this.a > 0.03) {
                this.w += this.vw;
                this.h += this.vh;
                if (this.w > 80) { // Slightly smaller splash
                    this.a *= this.va;
                    this.vw *= 0.98;
                    this.vh *= 0.98;
                }
            } else {
                this.init();
            }
        }
    }
}

function resize() {
    w = canvas.width = window.innerWidth;
    h = canvas.height = window.innerHeight;
}

function setup() {
    for (let i = 0; i < maxDrops; i++) {
        setTimeout(() => {
            drops.push(new RainDrop());
        }, i * 60);
    }
}

function animate() {
    ctx.fillStyle = clearColor;
    ctx.fillRect(0, 0, w, h);
    drops.forEach(drop => drop.draw());
    requestAnimationFrame(animate);
}

// Interactivity effects
window.addEventListener('scroll', () => {
    const scroll = window.scrollY;

    // Nav background toggle
    const nav = document.getElementById('navbar');
    if (scroll > 50) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
});

// Subtle Hero Parallax
document.addEventListener('mousemove', (e) => {
    if (window.innerWidth > 768) {
        const moveX = (e.clientX - window.innerWidth / 2) * 0.012;
        const moveY = (e.clientY - window.innerHeight / 2) * 0.012;
        const hero = document.querySelector('.hero-content');
        if (hero) {
            hero.style.transform = `translate(${moveX}px, ${moveY}px)`;
        }
    }
});

window.addEventListener("resize", resize);

setup();
animate();
