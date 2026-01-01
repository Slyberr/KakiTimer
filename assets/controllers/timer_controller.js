
import { Controller } from '@hotwired/stimulus';
import { randomScrambleForEvent } from "https://cdn.cubing.net/v0/js/cubing/scramble";


const NOT_READY = "NOT_READY";
const READY = "READY";
const RUNNING = "RUNNING";
const HOUR = 3600000000;
const MIN = 60000;

export default class extends Controller {

    static targets = ["timer","scramble","solves","averages","listofevents"];
    static values = { url: String};

    connect() {
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

    async stop() {
        this.timerRunning = false;
        clearInterval(this.intervalID);
        this.actualState = NOT_READY;
        console.log("Temps final : " + this.timerTarget.innerText);
        await this.refreshScramble();
        this.saveTime(this.timerTarget.innerText);
        this.calculateAverages(this.solvesTarget)
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

    async refreshScramble() {
        try {
            let newScramble = "";
            if (this.listofeventsTarget.value == "3x3") {

                const response = await fetch(this.urlValue);
                const data = await response.json();
                newScramble = data.newScramble;
            }else {
                const result = await randomScrambleForEvent(this.listofeventsTarget.value);
                newScramble = result.toString();
                
            }
            this.scrambleTarget.innerText = newScramble;
            
        }catch (error) {
            this.scrambleTarget.innerText = "Error Generating scramble."
        }
    }

    saveTime(time) { 
        this.solvesTarget.innerHTML += `<span>${time}</span>`;
    }

    calculateAverages(times) {
        let allTimes = this.solvesTarget.children;
        let mappedsAllTimes = Array.from(allTimes).map(x => parseFloat(x.innerText));
        if (mappedsAllTimes.length >=5) {
            this.calculateAverage(mappedsAllTimes.slice(mappedsAllTimes.length-5,mappedsAllTimes.length),5)
        }
        if (mappedsAllTimes.length >=12) {
            this.calculateAverage(mappedsAllTimes.slice(mappedsAllTimes.length-12,mappedsAllTimes.length),12)
        }

    }

    calculateAverage(times, count) {

        let orderTimes = times.sort()
        let timeForCal = orderTimes.slice(1,orderTimes.length-1);
        let sum = timeForCal.reduce((acc,currentVal) => acc + currentVal,0);

        this.averagesTarget.querySelector('#ao'+ count).innerText = `ao${count} : ${(sum / timeForCal.length).toFixed(2)}`;

    }
}
