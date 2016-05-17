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

    //TODO: Clean up this mess
    private API_URL = 'http://localhost:8888/angular-2-mathfinalexams/api/mathFinals.php'
    private dataUrl = 'http://localhost:8888/angular-2-mathfinalexams/api/mathFinals.php?enrollment=availability'; //URL to web api
    // private dataUrl = '../api/mockSessionData.json';
    //These two mock data sources are used to test the check date functionality
    // private singleSessionUrl = '../api/mockSingleStudentSession.json'; //one result
    // private singleSessionUrl = '../api/mockNoStudentSession.json';  //no result

    getStudentEnrollmentList(): Observable<Student[]> {
        let params = new URLSearchParams();
        params.set('enrollment', 'all');
        return this.http.get(this.API_URL, {search: params})
            .map(this.extractStudentEnrollmentListData)
            .catch(this.handleError);
    }

    private extractStudentEnrollmentListData(res: Response) {
        if (res.status < 200 || res.status > 300) {
            throw new Error('Bad response status: ' + res.status);
        }
        let body = res.json();
        console.log('The response that came back was');
        console.log(body);
        if (body.status === 'error') {
            throw new Error('Error: ' +body.message );
        }
        let students: Student[] = [];
        for (let item of body.data) {

            let examSession = new ExamSession(item.examSessionId, item.dateTime);

            students.push(
                new Student(item.studentId,
                    item.firstName,
                    item.lastName,
                    item.courseCrn,
                    item.examSessionId,
                    examSession)
            );

        }
        return students || {};
    }

    getExamSessions(): Observable<ExamSession[]> {
        //let parameters = '';
        return this.http.get(this.dataUrl)
            .map(this.extractMultipleSessionsData)
            .catch(this.handleError);

    }

    getFinalExamSession(student:Student):Observable<{student: Student, session: ExamSession}> {
        //TODOD: Add parameters to this request to make sure we get the correct student
        let params = new URLSearchParams();
        params.set('enrollment', 'single');
        params.set('crn', student.crn);
        params.set('csid', student.id);
        // console.log('Getting final exam session for student');
        // console.log(student);
        // console.log('The params that we are sending are');
        // console.log(params);
        return this.http.get(this.API_URL, {search: params})
            .map(this.extractSessionData)
            .catch(this.handleError)
    }

    extractSessionData(res: Response) {
        //console.log('The response object is (should be a single exam session)');
        //console.log(res);
        if (res.status < 200 || res.status > 300) {
            throw new Error('Bad response status: ' + res.status);
        }
        let body = res.json();
        //console.log('The response that came back was');
        //console.log(body);
        if (body.status === 'error') {
            throw new Error('Error: ' +body.message );
        }
        if (body.data) {
            let single = body.data[0];
            let student = new Student(single.studentId,
                single.firstName,
                single.lastName,
                single.courseCrn,
                single.examSessionId);

            let examSession = new ExamSession(single.examSessionId, single.dateTime, single.seatsAvailable);
            var result = {
                student: student,
                session: examSession
            }
        } else {
            console.log('Oh no! Empty response!!!');
        }
        return result || {};
    }

    addStudent(student:Student, action:string) : Observable<Student> {
        console.log('Adding student:');
        console.log(student);
        let body = student;
        body['action'] = action;
        let query = '';
        for (var key in body) {
            query += encodeURIComponent(key)+"="+encodeURIComponent(body[key])+"&";
        }
        query = query.substring(0, query.length - 1);
        console.log('The body of the post request is');
        console.log(query);
        let headers = new Headers({ 'Content-Type': 'application/x-www-form-urlencoded' });
        let options = new RequestOptions({ headers: headers });
        if (action === 'add') {
            return this.http.post(this.API_URL, query, options)
                .map(this.extractData)
                .catch(this.handleError);
        } else if (action === 'change') {
            console.log('changing student');
            return this.http.post(this.API_URL, query, options)
                .map(this.extractData)
                .catch(this.handleError);
        } else {
            console.log('There seems to be an error');
        }
    }

    private extractData(res: Response) {
        console.log('The response object (from extractData) is');
        console.log(res);
        if (res.status < 200 || res.status > 300) {
            throw new Error('Bad response status: ' + res.status);
        }
        let body = res.json();
        //console.log('The response that came back(from changing or adding the student) was');
        //console.log(body);

        if (body.status === 'error') {
            throw new Error('Error: ' +body.message );
        }

        return body.data || {};
    }



    private extractMultipleSessionsData(res: Response) {
        //console.log('The response object is');
        //console.log(res);
        if (res.status < 200 || res.status > 300) {
            throw new Error('Bad response status: ' + res.status);
        }
        let body = res.json();

        if (body.status === 'error') {
            throw new Error('Error: ' +body.message );
        }
        //console.log('The response that came back was');
        //console.log(body);
        //let examSessions = this.convertDataToExamSession(body);
        let examSessions: ExamSession[] = [];
        for (let item of body.data) {
            //console.log(item);
            examSessions.push(
                new ExamSession(item.examSessionId, item.dateTime, item.seatsAvailable)
            );
        }
        return examSessions || {};
    }

    private handleError(error: any) {
        console.log('There was an error');
        let errorMsg = error.message;
        console.log(errorMsg);
        //console.log(error);
        return Observable.throw(errorMsg);
    }
}