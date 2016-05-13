import {Injectable} from 'angular2/core';
import {Http, Response} from 'angular2/http';
import {ExamSession} from '../exam-session';
import {Student} from '../student';
import {Observable} from "rxjs/Observable";
import {Headers, RequestOptions, URLSearchParams} from "angular2/http";
//If we are going to test locally we need to use JSONP


@Injectable()
export class DataService {
    constructor(private http:Http) { }

    private API_URL = 'http://localhost:8888/angular-2-mathfinalexams/api/mathFinals.php'
    private dataUrl = 'http://localhost:8888/angular-2-mathfinalexams/api/mathFinals.php?enrollment=availability'; //URL to web api
    private singleSessionUrl = 'http://localhost:8888/math_final_exams-API/api/mathFinals.php?enrollment=single&crn=123123&csid=111111';
    // private dataUrl = '../api/mockSessionData.json';
    //These two mock data sources are used to test the check date functionality
    // private singleSessionUrl = '../api/mockSingleStudentSession.json'; //one result
    // private singleSessionUrl = '../api/mockNoStudentSession.json';  //no result

    getExamSessions(): Observable<ExamSession[]> {
        //let parameters = '';
        return this.http.get(this.dataUrl)
            .map(this.extractData)
            .catch(this.handleError);

    }

    getFinalExamSession(student:Student):Observable<ExamSession[]> {
        //TODOD: Add parameters to this request to make sure we get the correct student
        let params = new URLSearchParams();
        params.set('enrollment', 'single');
        params.set('crn', student.crn);
        params.set('csid', student.id);
        console.log('Getting final exam session for student');
        console.log(student);
        console.log('The params that we are sending are');
        console.log(params);
        return this.http.get(this.API_URL, {search: params})
            .map(this.extractData)
            .catch(this.handleError)
    }
    

    addStudent(student: Student) : Observable<Student> {
        console.log('Adding student:');
        console.log(student);
        let body = JSON.stringify(student);
        let headers = new Headers({ 'Content-Type': 'application/json' });
        let options = new RequestOptions({headers: headers});

        return this.http.post(this.dataUrl, body, options)
                        .map(this.extractData)
                        .catch(this.handleError);
    }

    private extractData(res: Response) {
        if (res.status < 200 || res.status > 300) {
            throw new Error('Bad response status: ' + res.status);
        }
        let body = res.json();
        console.log('The response that came back was');
        console.log(body);
        //let examSessions = this.convertDataToExamSession(body);
        let examSessions: ExamSession[] = [];
        for (let item of body.data) {
            console.log(item);
            examSessions.push(
                new ExamSession(item.examSessionId, item.dateTime, item.seatsAvailable)
            );
        }
        return examSessions || {};
    }

    private convertDataToExamSession(body) {
        let examSessions: ExamSession[];
        for (let item of body.data) {
            //console.log(item);
            examSessions.push(
                new ExamSession(item.id, item.dateTime, item.seatsAvailable)
            );
        }
        console.log(examSessions);
        return examSessions;
    }

    private handleError(error: any) {
        console.log('There was an error');
        let errorMsg = error.message;
        console.log(errorMsg);
        return Observable.throw(errorMsg);
    }
}