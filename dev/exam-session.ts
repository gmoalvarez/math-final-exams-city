export class ExamSession {
    id: string;
    date: string;
    time: string;
    seatsAvailable: number;

    constructor(
        public id: string,
        public dateTime: string,
        public seatsAvailable: number
    ) {
        this.id = id;
        this.seatsAvailable = seatsAvailable;
        this.date = dateTime.split(' ')[0];
        this.time = dateTime.split(' ')[1];
    }
}