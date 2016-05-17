import {Component, OnInit} from "angular2/core";
import {Student} from "./Student";
import {DataService} from "./shared/data.service";
@Component({
    selector: 'my-student-list',
    template: `
    <input type="text">
    <table class="table-responsive table-striped">
    <tr>
        <th>Last Name</th>
        <th>First Name</th>
        <th>Class</th>
        <th>Date</th>
        <th>Time</th>
        <th>Location</th>
    </tr>
    <tr *ngFor="#student of filteredStudents">
        <td>{{student.lastName}}</td>
        <td>{{student.firstName}}</td>
        <td>Math</td>
        <td>{{student.examSession.date}}</td>
        <td>{{student.examSession.time}}</td>
        <td>MS Building</td>
    </tr>
    </table>
    `,
    providers: [DataService]
})

export class StudentListComponent implements OnInit{
    

    students: Student[];
    filteredStudents: Student[];
    // examSessions: ExamSession[];
    errorMessage: string;
    constructor(private dataService: DataService) {}
    

    ngOnInit() {
        this.getStudents();
    }

    getStudents() {
        this.dataService.getStudentEnrollmentList()
            .subscribe(
                students => {
                    this.students = students;
                    this.filteredStudents = students; //start off with all students being filtered
                    console.log(students);
                },
                error => this.errorMessage = error
            )
    }
}