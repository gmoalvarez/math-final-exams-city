import {Component} from "angular2/core";
import {Student} from "./student";
import {NgForm} from "angular2/common";
import {ExamSession} from "./exam-session";
import {DataService} from "./shared/data.service";
import {OnInit} from "angular2/core";
import {Observable} from "rxjs/Observable";

@Component({
    selector: 'my-signup',
    templateUrl: 'app/signup-form.html',
    providers: [
        DataService
    ]
})

export class SignupFormComponent implements  OnInit{
    constructor(private dataService:DataService) {}

    student = new Student('', '', '', '', '');
    submitted = false;
    duplicate = false;
    sessions: ExamSession[];
    errorMessage: string;
    chosenSession:ExamSession = new ExamSession('','',0);

    ngOnInit() { this.getSessions()}

    getSessions() {
        this.dataService.getExamSessions()
            .subscribe(
                examSessions => {this.sessions = examSessions;
                    //console.log(examSessions);
                },
                error => this.errorMessage = error
            )
    }

    addStudent (student: Student) {
        this.dataService.addStudent(student, 'add')
            .subscribe(
                student => {
                    if (Object.keys(student).length === 0 && student.constructor === Object) {
                        console.log('Empty response from addStudent! This should never happen!!!!!');
                    }

                    this.chosenSession = this.sessions.filter(session=>session.id == student.examSessionId)[0];
                    this.duplicate = false;
                    this.submitted = true;
                },
                error => {
                    console.log('The error is');
                    console.log(error);
                    this.errorMessage = error;
                    this.duplicate = true;
                }
            );
    }

    onSubmit() {
        this.addStudent(this.student);
    }
}