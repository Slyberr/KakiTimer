import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ["solves","averages"];

    saveTime(time) {
        this.solvesTarget.innerHTML += `<span>${time}</span>`;
    }

    calculateAverages() {
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

}