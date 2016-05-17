import {ExamSession} from "./exam-session";
export class Student {

    constructor(
        public id: string,
        public firstName: string,
        public lastName: string,
        public crn: string,
        public examSessionId: string,
        public examSession?:ExamSession
    ) {}
}