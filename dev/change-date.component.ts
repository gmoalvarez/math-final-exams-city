import {Component} from "angular2/core";
import {Student} from "./Student";
import {ExamSession} from "./exam-session";
import {DataService} from "./shared/data.service";

@Component({
    selector: 'my-change-date',
    template: `
        <hr>
        <div class="container">
            <h3>Would you like to change your exam date?</h3>
            <form  class="form" (ngSubmit)="onSubmit()" #changeDateForm="ngForm">
                <div class="form-group">
                    <label for="session">Final Exam Session</label>
                    <select [(ngModel)]="student.examSessionId"
                            ngControl="examSessionId" #examSessionId="ngForm"
                            class="form-control" required>
                        <option *ngFor="#session of sessions" [value]="session.id">
                            <strong>Date: </strong> {{session.date}},
                            <strong>Time: </strong> {{session.time}},
                            <span class="label-default label">{{session.seatsAvailable}} seats available</span>
                        </option>
                    </select>
                    <div [hidden]="examSessionId.valid || examSessionId.pristine"
                         class="alert alert-danger">
                        Exam Session is required
                    </div>
                </div>
                <br>
                <button type="submit" class="btn btn-default"
                        [disabled]="!changeDateForm.form.valid">Submit
                </button>
            </form>
        </div>
    `,
    providers: [DataService],
    directives: []
})

export class ChangeDateComponent {

    constructor(private dataService: DataService) {}

    student = new Student('', '', '', '', '');
    errorMessage: string;
    submitted = false;
    sessions: ExamSession[];

    getSessions() {
        this.dataService.getExamSessions()
            .subscribe(
                examSessions => {this.sessions = examSessions;
                    //console.log(examSessions);
                },
                error => this.errorMessage = error
            )
    }

    onSubmit() {
        this.submitted = true;
    }

}