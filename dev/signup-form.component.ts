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

    student = new Student('111111', 'Guillermo', 'Alvarez', '12343', '1');
    submitted = false;
    duplicate = false;
    sessions: ExamSession[];
    errorMessage: string;
    chosenSession:ExamSession;

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
                    console.log('The response from addStudent is');
                    console.log(student);
                    if (Object.keys(student).length === 0 && student.constructor === Object) {
                        console.log('Empty response!');
                        console.log('We need to do something about this');
                    }
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