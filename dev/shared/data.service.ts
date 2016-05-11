import {Injectable} from 'angular2/core';
import {Http, Response} from 'angular2/http';
import {ExamSession} from '../exam-session';
import {Student} from '../student';
import {Observable} from "rxjs/Observable";

@Injectable()
export class DataService {
    constructor(private http:Http) { }

    //private dataUrl = '../api/mathFinals.php'; //URL to web api
    private dataUrl = '../api/mockSessionData.json';

    getExamSessions(): Observable<ExamSession[]> {
        //let parameters = '';
        return this.http.get(this.dataUrl)
            .map(this.extractData)
            .catch(this.handleError);
    }

    private extractData(res: Response) {
        if (res.status < 200 || res.status > 300) {
            throw new Error('Bad response status: ' + res.status);
        }
        let body = res.json();
        //let examSessions = this.convertDataToExamSession(body);
        let examSessions: ExamSession[] = [];
        for (let item of body) {
            console.log(item);
            examSessions.push(
                new ExamSession(item.examSessionId, item.dateTime, item.seatsAvailable)
            );
        }
        return examSessions || {};
    }

    private convertDataToExamSession(body) {
        let examSessions: ExamSession[];
        for (let item of body) {
            console.log(item);
            examSessions.push(
                new ExamSession(item.id, item.dateTime, item.seatsAvailable)
            );
        }
        console.log(examSessions);
        return examSessions;
    }

    private handleError(error: any) {
        let errorMsg = error.message;
        console.log(errorMsg);
        return Observable.throw(errorMsg);
    }
}