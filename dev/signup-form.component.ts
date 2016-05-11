import {Component} from "angular2/core";
import {Student} from "./student";
import {NgForm} from "angular2/common";
import {ExamSession} from "./exam-session";

@Component({
    selector: 'my-signup',
    templateUrl: 'app/signup-form.html'
})

export class SignupFormComponent {
    student = new Student('111111', 'Guillermo', 'Alvarez', '12343', '1');
    submitted = false;
    sessions = [
        new ExamSession('1', '05/19/2016', '03:00 PM', 40),
        new ExamSession('2', '05/19/2016', '04:00 PM', 40 ),
        new ExamSession('3', '05/19/2016', '05:00 PM', 40 ),
        new ExamSession('4', '05/20/2016', '03:00 PM', 40 ),
        new ExamSession('5', '05/20/2016', '04:00 PM', 40 ),
        new ExamSession('6', '05/20/2016', '05:00 PM', 40 ),
    ]

    onSubmit() {
        this.submitted = true
    }

    // TODO: Remove this when we're done
    get diagnostic() {
        return JSON.stringify(this.student);
    }
}