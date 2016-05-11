import {Component} from "angular2/core";
import {DataService} from "./shared/data.service";
import {Student} from "./Student";
import {ExamSession} from "./exam-session";

@Component({
    selector: 'my-check-date',
    template: `
        <div class="container">
            <h1>Check final exam date</h1>
            <div [hidden]="submitted">
                <form class="form" (ngSubmit)="onSubmit()" #checkDateForm="ngForm">
                    <div class="form-group">
                        <label for="csid">Student Id</label>
                        <input [(ngModel)]="student.id"
                               ngControl="studentId" #studentId="ngForm"
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
            <div [hidden]="!submitted">
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
                </div>
                <div *ngIf="!found">
                    <h1>Did not find student in this course</h1>
                </div>
            </div>
        </div>

    `,
    providers: [DataService]
})

export class CheckDateComponent {
    constructor(private dataService: DataService) {}

    student = new Student('','','','','');
    submitted = false;
    found = false;
    session: ExamSession;
    errorMessage: string;

    onSubmit() {
        
        this.submitted = true;
    }
}