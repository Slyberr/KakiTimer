import { Controller } from '@hotwired/stimulus';
const NOT_READY = "NOT_READY";
const READY = "READY";
const RUNNING = "RUNNING";
const HOUR = 3600000000;
const MIN = 60000;

export default class extends Controller {



    static targets = ["timer"];



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

    stop() {
        this.timerRunning = false;
        clearInterval(this.intervalID);
        this.actualState = NOT_READY;
        console.log("Temps final : " + this.timerTarget.innerText);
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
