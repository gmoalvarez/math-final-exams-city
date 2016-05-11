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
    sessions: ExamSession[];
    errorMessage: string;

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

    //TODO: Right now we are not doing anything with the student that was added. In the future we might want to update
    //a list of students that is saved somewhere. Maybe the one that has the list of all students enrolled in the exam
    addStudent (student: Student) {
        this.dataService.addStudent(student)
                        .subscribe(
                            student => {},
                            error => this.errorMessage = <any>error
                        );
    }

    onSubmit() {
        this.submitted = true
    }



    // TODO: Remove this when we're done
    get diagnostic() {
        return JSON.stringify(this.student);
    }
}