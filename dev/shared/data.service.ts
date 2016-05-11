import {Injectable} from 'angular2/core';
import {Http} from 'angular2/http';
import {ExamSession} from '../exam-session';
import {Student} from '../student';
import {Observable} from "rxjs/Observable";

@Injectable()
export class DataService {
    constructor(private http:Http) { }

    private dataUrl = '../api/mathFinals.php'; //URL to web api

    getExamSessions(): Observable<ExamSession[]> {
        parameters =
    }
}