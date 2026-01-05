
import { Controller } from '@hotwired/stimulus';
import { randomScrambleForEvent } from 'cubing/scramble';
import { Exception } from 'sass';

const NOT_READY = "NOT_READY";
const READY = "READY";
const RUNNING = "RUNNING";
const HOUR = 3600000000;
const MIN = 60000;

export default class extends Controller {

    static targets = ["timer", "scramble", "solves", "averages", "listofevents"];
    static values = { url: String };

    //Appelé au chargement de la page
    async connect() {

        await this.refreshAndDrawScramble();
        this.timerRunning = false;
        //lors du chargement de la page, le timer est prêt.
        this.actualState = READY;

        window.addEventListener('keyup', (event) => {
            this.keyup(event);
        })

        window.addEventListener('keydown', (event) => {
            this.keydown(event);
        })

    }

    keyup(event) {
        if (event.code === "Space") {

            switch (true) {
                case (this.actualState == NOT_READY && !this.timerRunning):
                    this.actualState = READY;
                    break;

                case (this.actualState == READY && !this.timerRunning):
                    this.start();
                    break;

                default:
                    break;
            }
        }

        if (event.code === "Escape") {
            this.timerTarget.innerText = "0.00";
        }
    }

    keydown(event) {
        if (event.code === "Space" && this.timerRunning && this.actualState == RUNNING) {
            this.stop();
        }
    }


    //Quand le timer est lancé
    start() {
        //performance.now() permet d'assurer une précision à l'ordre de la microseconde et 
        //ne dépend pas du temps d'exécution du code.

        this.timerRunning = true;
        this.timerTarget.innerText = "0.00";
        let actualTime = performance.now();
        this.actualState = RUNNING;

        this.intervalID = setInterval(() => {
            const elapsed = (performance.now() - actualTime);
            this.timerTarget.innerText = this.hourMinSecFormat(elapsed)
        }
            , 10)
    }

    //Quand le timer doit s'arrêter
    async stop() {
        this.timerRunning = false;
        this.actualState = NOT_READY;
        clearInterval(this.intervalID);

        await this.refreshAndDrawScramble();
        this.saveTime(this.timerTarget.innerText);
        this.calculateAverages(this.solvesTarget)
    }

    async refreshAndDrawScramble() {

        const newScramble = await this.refreshScramble();
        const draw = await this.drawScramble(newScramble);

        this.scrambleTarget.innerText = newScramble;

        document.styleSheets.
        document.getElementById('cube-drawed').innerHTML = draw;
       
    }

    async refreshScramble() {

        let theEvent = this.listofeventsTarget.value;
        try {

            let newScramble = "";
            this.scrambleTarget.innerText = "Génération du mélange en cours..."

            //Je ne gère que la génération de 3x3 pour l'instant, je délègue sinon.
            if (theEvent !== "333") {
                newScramble = await randomScrambleForEvent(theEvent);
                newScramble = newScramble.toString();
            } else {
                const params = new URLSearchParams ({
                    event : theEvent
                })
                const urlWithParams = this.urlValue + `?${params.toString()}`;

                const response = await fetch(urlWithParams);
                const data = await response.json();
                newScramble = data.newScramble;
                newScramble = newScramble.toString();
            }            
            
            return newScramble;

        } catch (error) {
            this.scrambleTarget.innerText = "Aucun mélange généré."
           console.error(error.message);
        }
    }

    async drawScramble(scramble) {
        
        const eventType = this.listofeventsTarget.value;
        let cube = [];

        if (eventType === '333' || eventType === '444' || eventType === '555' || eventType === '666' || eventType === '777' || eventType === '222') {
            try {
                const urlValue = "/timer/scramble/draw";
                const params = new URLSearchParams({
                    event : eventType,
                    scramble : scramble
                })

                const urlWithParam = urlValue + `?${params.toString()}`
                const response = await fetch(urlWithParam);
                const data = await response.json();
                let cube = data.cubeScrambled;
                return this.renderCubeDraw(cube);
            }catch(error) {
                document.getElementById('cube-drawed').textContent = "Problème lors de l'affichage du dessin.";
                console.error(error.message);
            }
        }

    }

    renderCubeDraw(cube) {
        let HTMLstructure = "";
        HTMLstructure += '<div class="cube-scrambled">';
        let acc=0;
        for (const [face,tabstickers] of Object.entries(cube)) {


            if (acc == 2) {
                HTMLstructure += '<div class="the-line-on-patron">';
            }

            HTMLstructure += '<div class="face">';
            for (const sticker of tabstickers) {

                let color = sticker;
                HTMLstructure += `<span style='display:inline-block;width:15px;height:15px;background-color:${color};border:1px black solid'></span>`
            
            }
            HTMLstructure += '</div>';

            //the-line-on-patron
            if (acc == 5){
                HTMLstructure += '</div>'
            }
            acc++;
        }
        //cube-scrambled
        HTMLstructure +='</div>';
        return HTMLstructure;
    }


    saveTime(time) {
        this.solvesTarget.innerHTML += `<span>${time}</span>`;
    }

    calculateAverages(times) {
        let allTimes = this.solvesTarget.children;
        let mappedsAllTimes = Array.from(allTimes).map(x => parseFloat(x.innerText));
        if (mappedsAllTimes.length >= 5) {
            this.calculateAverage(mappedsAllTimes.slice(mappedsAllTimes.length - 5, mappedsAllTimes.length), 5)
        }
        if (mappedsAllTimes.length >= 12) {
            this.calculateAverage(mappedsAllTimes.slice(mappedsAllTimes.length - 12, mappedsAllTimes.length), 12)
        }

    }

    calculateAverage(times, count) {

        let orderTimes = times.sort()
        let timeForCal = orderTimes.slice(1, orderTimes.length - 1);
        let sum = timeForCal.reduce((acc, currentVal) => acc + currentVal, 0);

        this.averagesTarget.querySelector('#ao' + count).innerText = `ao${count} : ${(sum / timeForCal.length).toFixed(2)}`;

    }

    //Transformation du temps au format HH:MM:SS.mm
    hourMinSecFormat(time) {

        let hours = null;
        let minutes = null;
        let seconds = null;
        let valueToPrint = "";

        if (time >= HOUR) {
            hours == ~~(time / HOUR)
            time = time - (hours * HOUR);
            valueToPrint += `${hours}:`;

        }
        if (time >= MIN) {
            minutes = ~~(time / MIN) % MIN;
            time = time - (minutes * MIN);
            if (hours > 0 && minutes < 10) {
                valueToPrint += `0${minutes}:`;
            } else {
                valueToPrint += `${minutes}:`;
            }
        }
        seconds = (time / 1000).toFixed(2)
        if (minutes > 0 && seconds < 10) {
            valueToPrint += `0`;
        }

        valueToPrint += seconds;
        return valueToPrint;
    }
}
    
