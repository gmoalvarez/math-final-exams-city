export class ExamSession {
    constructor(
        public id: string,
        public date: string,
        public time: string,
        public seatsAvailable: number
    ) {}
}