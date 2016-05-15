import {Component} from "angular2/core";
import {DataService} from "./shared/data.service";
import {Student} from "./Student";
import {ExamSession} from "./exam-session";
import {ChangeDateComponent} from "./change-date.component";

@Component({
    selector: 'my-check-date',
    template: `
        <div  class="container">
            <h1>Check final exam date</h1>
            <div >
                <form [hidden]="(submitted && found)" class="form" (ngSubmit)="onSubmit()" #checkDateForm="ngForm">
                    <div class="form-group">
                        <label for="csid">Student Id</label>
                        <input [(ngModel)]="student.id"
                               ngControl="csid" #studentId="ngForm"
                               type="text" class="form-control" required>
                        <div [hidden]="studentId.valid || studentId.pristine"
                             class="alert alert-danger">
                            Student ID is required
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="crn">Course CRN</label>
                        <input [(ngModel)]="student.crn"
                               ngControl="crn" #crn="ngForm"
                               type="text" class="form-control" required>
                        <div [hidden]="crn.valid || crn.pristine"
                             class="alert alert-danger">
                            Course CRN is required
                        </div>
                    </div>
                    
                    <div [hidden]="crn.valid || crn.pristine"
                         class="alert alert-danger">
                             Course CRN is required
                    </div>
                    <br>
                    <button type="submit" class="btn btn-default"
                            [disabled]="!checkDateForm.form.valid">Submit
                    </button>
                </form>
            </div>
        
            <!--Confirmation Message-->
            <div [hidden]="!submitted || !found">
                <h2>You submitted the following:</h2>
                <div class="row">
                    <div class="col-xs-3">Student ID</div>
                    <div class="col-xs-9  pull-left">{{ student.id }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Course CRN</div>
                    <div class="col-xs-9  pull-left">{{ student.crn }}</div>
                </div>
                
                <div *ngIf="found">
                    <h1>Found student</h1>
                    <div class="row">
                        <div class="col-xs-3">Name</div>
                        <div class="col-xs-9">{{student.firstName}} {{student.lastName}}</div>
                    </div>
                    <div class="row">
                        <div class="col-xs-3">Exam Session Number</div>
                        <div class="col-xs-9">{{session.id}}</div>
                    </div>
                    <div class="row">
                        <div class="col-xs-3">Date</div>
                        <div class="col-xs-9">{{session.date}}</div>
                    </div>
                    <div class="row">
                        <div class="col-xs-3">Time</div>
                        <div class="col-xs-9">{{session.time}}</div>
                    </div>
                </div>
                
                <my-change-date [checkDateStudent]="student"
                                [oldSession]="session"
                                (examSessionChanged)="session = $event"
                                ></my-change-date>

            </div>
            <div *ngIf="!found && submitted">
                <h1>Did not find student in this course</h1>
            </div>
        </div>

    `,
    directives: [ChangeDateComponent],
    providers: [DataService]
})

export class CheckDateComponent {
    constructor(private dataService: DataService) {}

    student = new Student('','','','','');
    submitted = false;
    found = false;
    session: ExamSession;
    errorMessage: string;

    getSessionDetails() {
        this.dataService.getFinalExamSession(this.student)
            .subscribe(
                result => {
                    interface result {
                        student: Student,
                        session: ExamSession
                    }
                    console.log('The result that came back (from getFinalExamSession) is');
                    console.log(result);
                    this.session = result.session;
                    this.student = result.student;
                    this.submitted = true;
                    if (this.session && this.student) {
                        console.log('Found the session');
                        this.found = true;
                        console.log('It is ');
                        console.log(this.session);
                        console.log(this.student);
                    } else {
                        this.found = false;
                    }
                    console.log('The student now contains');
                    console.log(this.student);
                },
                error => this.errorMessage = <any>error
            );
    }

    onSubmit() {
        this.getSessionDetails();
    }
}